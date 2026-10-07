<?php
require_once '../includes/config.php';
require_role('doctor');

// Resolve the logged-in doctor's doctors.id from their session user_id
$doc_stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$doc_stmt->bind_param("i", $_SESSION['user_id']);
$doc_stmt->execute();
$doctor = $doc_stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $doctor_id) {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);

    if ($appointment_id > 0) {
        // Ownership + current-status check happens directly in the UPDATE itself:
        // only a 'booked' appointment belonging to THIS doctor can be cancelled.
        $stmt = $conn->prepare("
            UPDATE appointments
            SET status = 'cancelled'
            WHERE id = ?
            AND doctor_id = ?
            AND status = 'booked'
        ");
        $stmt->bind_param("ii", $appointment_id, $doctor_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: dashboard.php?msg=cancelled");
            exit();
        } else {
            // Either it wasn't this doctor's appointment, it wasn't 'booked',
            // or the ID didn't exist — all treated the same way for safety.
            header("Location: dashboard.php?error=cancel_failed");
            exit();
        }
    }
}

header("Location: dashboard.php?error=cancel_failed");
exit();
?>
