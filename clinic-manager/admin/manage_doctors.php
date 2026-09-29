```php
<?php
require_once '../includes/config.php';
require_role('admin');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Delete doctor
    if (isset($_POST['delete_doctor'])) {
        $doctor_id = intval($_POST['doctor_id']);

        $conn->begin_transaction();

        try {
            // Get user_id of doctor
            $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id = ?");
            $stmt->bind_param("i", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($doctor = $result->fetch_assoc()) {
                $user_id = $doctor['user_id'];

                // Delete doctor record
                $stmt2 = $conn->prepare("DELETE FROM doctors WHERE id = ?");
                $stmt2->bind_param("i", $doctor_id);
                $stmt2->execute();

                // Delete doctor user account
                $stmt3 = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
                $stmt3->bind_param("i", $user_id);
                $stmt3->execute();

                $conn->commit();
                $message = "<p class='success'>Doctor deleted successfully.</p>";
            } else {
                throw new Exception("Doctor not found.");
            }

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p class='error'>Failed to delete doctor. The doctor may have related records.</p>";
        }
    }

    // Add doctor
    else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $specialization = trim($_POST['specialization']);
        $bio = trim($_POST['bio']);

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password_hash, role)
                 VALUES (?, ?, ?, 'doctor')"
            );
            $stmt->bind_param("sss", $name, $email, $hash);
            $stmt->execute();

            $user_id = $conn->insert_id;

            $stmt2 = $conn->prepare(
                "INSERT INTO doctors (user_id, specialization, bio)
                 VALUES (?, ?, ?)"
            );
            $stmt2->bind_param("iss", $user_id, $specialization, $bio);
            $stmt2->execute();

            $conn->commit();
            $message = "<p class='success'>Doctor added successfully.</p>";

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p class='error'>Failed to add doctor. Email may already exist.</p>";
        }
    }
}

$doctors = $conn->query(
    "SELECT d.id, u.name, u.email, d.specialization
     FROM doctors d
     JOIN users u ON d.user_id = u.id"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Doctors</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</div>

<div class="container">

    <h2>Add New Doctor</h2>

    <?= $message ?>

    <form method="POST">

        <div class="form-group">
            <label>Name *</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Specialization *</label>
            <input type="text" name="specialization" required>
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" rows="3"></textarea>
        </div>

        <button type="submit">Add Doctor</button>

    </form>

    <h2 style="margin-top:30px;">Existing Doctors</h2>

    <table>

        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Specialization</th>
            <th>Action</th>
        </tr>

        <?php while ($d = $doctors->fetch_assoc()): ?>

        <tr>

            <td><?= htmlspecialchars($d['name']) ?></td>

            <td><?= htmlspecialchars($d['email']) ?></td>

            <td><?= htmlspecialchars($d['specialization']) ?></td>

            <td>

                <form method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this doctor?');">

                    <input type="hidden"
                           name="doctor_id"
                           value="<?= $d['id'] ?>">

                    <button type="submit"
                            name="delete_doctor"
                            style="background:#dc3545;">
                        🗑 Delete
                    </button>

                </form>

            </td>

        </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>
</html>
```
