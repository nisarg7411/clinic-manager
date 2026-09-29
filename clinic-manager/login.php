<?php
require_once 'includes/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'patient') header("Location: patient/dashboard.php");
            elseif ($row['role'] === 'doctor') header("Location: doctor/dashboard.php");
            else header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Clinic Manager</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Mini Clinic Manager</strong>
    <div><a href="register.php">Register</a></div>
</div>

<div class="container" style="max-width:400px;">
    <h2>Login</h2>
    <?php if (isset($_GET['registered'])): ?><p class="success">Registration successful! Please login.</p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
