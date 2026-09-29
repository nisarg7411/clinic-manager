<?php
require_once '../includes/config.php';
require_role('doctor');

// get doctor row id from user_id
$doc_stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$doc_stmt->bind_param("i", $_SESSION['user_id']);
$doc_stmt->execute();
$doctor = $doc_stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT a.id, u.name AS patient_name, a.appointment_date, a.time_slot, a.status
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date ASC, a.time_slot ASC
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div>
        <span>Dr. <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>My Appointments</h2>
    <table>
        <tr><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $appointments->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['patient_name']) ?></td>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars($row['time_slot']) ?></td>
            <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td>
                <?php if ($row['status'] === 'booked'): ?>
                    <a href="add_prescription.php?appointment_id=<?= $row['id'] ?>">Add Prescription</a>
                <?php else: ?>
                    <a href="add_prescription.php?appointment_id=<?= $row['id'] ?>">View</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
