<?php
require_once '../includes/config.php';
require_role('doctor');

$appointment_id = intval($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = trim($_POST['notes']);
    $medicines = trim($_POST['medicines']);

    // Insert or update prescription
    $check = $conn->prepare("SELECT id FROM prescriptions WHERE appointment_id = ?");
    $check->bind_param("i", $appointment_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $upd = $conn->prepare("UPDATE prescriptions SET notes = ?, medicines = ? WHERE appointment_id = ?");
        $upd->bind_param("ssi", $notes, $medicines, $appointment_id);
        $upd->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO prescriptions (appointment_id, notes, medicines) VALUES (?, ?, ?)");
        $ins->bind_param("iss", $appointment_id, $notes, $medicines);
        $ins->execute();
    }

    // Mark appointment as completed
    $mark = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
    $mark->bind_param("i", $appointment_id);
    $mark->execute();

    $message = "<p class='success'>Prescription saved successfully.</p>";
}

$data_stmt = $conn->prepare("
    SELECT a.appointment_date, a.time_slot, u.name AS patient_name, p.notes, p.medicines
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    LEFT JOIN prescriptions p ON p.appointment_id = a.id
    WHERE a.id = ?
");
$data_stmt->bind_param("i", $appointment_id);
$data_stmt->execute();
$data = $data_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Prescription</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div><a href="dashboard.php">Back to Dashboard</a></div>
</div>

<div class="container">
    <h2>Prescription for <?= htmlspecialchars($data['patient_name'] ?? '') ?></h2>
    <p><?= htmlspecialchars($data['appointment_date'] ?? '') ?> at <?= htmlspecialchars($data['time_slot'] ?? '') ?></p>
    <?= $message ?>

    <form method="POST">
        <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">
        <div class="form-group">
            <label>Consultation Notes</label>
            <textarea name="notes" rows="4"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Medicines</label>
            <textarea name="medicines" rows="4" placeholder="e.g. Paracetamol 500mg - twice daily"><?= htmlspecialchars($data['medicines'] ?? '') ?></textarea>
        </div>
        <button type="submit">Save & Mark Completed</button>
    </form>
</div>
</body>
</html>
