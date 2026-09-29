<?php
// ============================================
// Database Configuration
// Update these values to match your local XAMPP/hosting setup
// ============================================
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // change if your hosting gives different credentials
define('DB_PASS', '');           // change if your hosting gives different credentials
define('DB_NAME', 'clinic_manager');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper: redirect if not logged in / wrong role
function require_role($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header("Location: ../login.php");
        exit();
    }
}
?>
