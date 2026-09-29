<?php
require_once '../includes/config.php';
require_role('admin');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $day = $_POST['day_of_week'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $duration = intval($_POST['slot_duration']);

    if ($start >= $end) {
        $message = "<p class='error'>End time must be after start time.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $doctor_id, $day, $start, $end, $duration);
        $stmt->execute();
        $message = "<p class='success'>Availability added.</p>";
    }
}

$doctors = $conn->query("SELECT d.id, u.name FROM doctors d JOIN users u ON d.user_id = u.id");
$doctors_list = [];
while ($d = $doctors->fetch_assoc()) $doctors_list[] = $d;

$availability = $conn->query("
    SELECT u.name, da.day_of_week, da.start_time, da.end_time, da.slot_duration_minutes
    FROM doctor_availability da
    JOIN doctors d ON da.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    ORDER BY u.name, FIELD(da.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Availability</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div><a href="dashboard.php">Back to Dashboard</a></div>
</div>

<div class="container">
    <h2>Set Doctor Availability</h2>
    <?= $message ?>
    <form method="POST">
        <div class="form-group">
            <label>Doctor *</label>
            <select name="doctor_id" required>
                <?php foreach ($doctors_list as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Day of Week *</label>
            <select name="day_of_week" required>
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                    <option value="<?= $day ?>"><?= $day ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Start Time *</label><input type="time" name="start_time" required></div>
        <div class="form-group"><label>End Time *</label><input type="time" name="end_time" required></div>
        <div class="form-group"><label>Slot Duration (minutes) *</label><input type="number" name="slot_duration" value="30" min="10" required></div>
        <button type="submit">Add Availability</button>
    </form>

    <h2 style="margin-top:30px;">Current Availability</h2>
    <table>
        <tr><th>Doctor</th><th>Day</th><th>Start</th><th>End</th><th>Slot (min)</th></tr>
        <?php while ($a = $availability->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td><?= htmlspecialchars($a['day_of_week']) ?></td>
            <td><?= htmlspecialchars($a['start_time']) ?></td>
            <td><?= htmlspecialchars($a['end_time']) ?></td>
            <td><?= htmlspecialchars($a['slot_duration_minutes']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
