<?php
require_once '../includes/config.php';
require_role('patient');

$stmt = $conn->prepare("
    SELECT a.id, u.name AS doctor_name, d.specialization, a.appointment_date, a.time_slot, a.status
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.time_slot DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div>
        <span>Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>My Appointments</h2>
    <table>
        <tr><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Prescription</th></tr>
        <?php while ($row = $appointments->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
            <td><?= htmlspecialchars($row['specialization']) ?></td>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars($row['time_slot']) ?></td>
            <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td><a href="view_prescription.php?appointment_id=<?= $row['id'] ?>">View</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php if ($appointments->num_rows === 0): ?>
        <p>No appointments yet. <a href="book_appointment.php">Book one now</a>.</p>
    <?php endif; ?>
</div>
</body>
</html>
