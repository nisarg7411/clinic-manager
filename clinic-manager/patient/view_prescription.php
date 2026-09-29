<?php
require_once '../includes/config.php';
require_role('patient');

$appointment_id = intval($_GET['appointment_id'] ?? 0);

// Ensure this appointment belongs to the logged-in patient
$stmt = $conn->prepare("
    SELECT p.notes, p.medicines, p.created_at, u.name AS doctor_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    LEFT JOIN prescriptions p ON p.appointment_id = a.id
    WHERE a.id = ? AND a.patient_id = ?
");
$stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Prescription</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div><a href="dashboard.php">Back to Dashboard</a></div>
</div>

<div class="container">
    <h2>Prescription Details</h2>
    <?php if (!$result): ?>
        <p>Appointment not found.</p>
    <?php elseif (!$result['notes']): ?>
        <p>No prescription added yet by <?= htmlspecialchars($result['doctor_name']) ?>.</p>
    <?php else: ?>
        <p><strong>Doctor:</strong> <?= htmlspecialchars($result['doctor_name']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($result['created_at']) ?></p>
        <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($result['notes'])) ?></p>
        <p><strong>Medicines:</strong> <?= nl2br(htmlspecialchars($result['medicines'])) ?></p>
    <?php endif; ?>
</div>
</body>
</html>
