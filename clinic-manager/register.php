<?php
require_once 'includes/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if ($name && $email && $password) {
        // check duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered. Please login instead.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'patient')");
            $stmt2->bind_param("ssss", $name, $email, $hash, $phone);
            if ($stmt2->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    } else {
        $error = "Please fill all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Clinic Manager</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Mini Clinic Manager</strong>
    <div><a href="login.php">Login</a></div>
</div>

<div class="container" style="max-width:450px;">
    <h2>Patient Registration</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" id="phone" maxlength="10">
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" id="password" required minlength="6">
        </div>
        <button type="submit">Register</button>
    </form>
</div>

<script>
// Client-side validation (also re-validated on server via PHP)
function validateForm() {
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;

    if (phone && !/^\d{10}$/.test(phone)) {
        alert("Phone number must be 10 digits.");
        return false;
    }
    if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
