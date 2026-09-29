<?php
require_once '../includes/config.php';
require_role('admin');

$total_appointments = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()['c'];
$total_doctors = $conn->query("SELECT COUNT(*) AS c FROM doctors")->fetch_assoc()['c'];
$total_patients = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='patient'")->fetch_assoc()['c'];

$by_doctor = $conn->query("
    SELECT u.name, COUNT(a.id) AS total
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN appointments a ON a.doctor_id = d.id
    GROUP BY d.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div>
        <a href="manage_doctors.php">Manage Doctors</a>
        <a href="manage_availability.php">Set Availability</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Admin Dashboard</h2>
    <p><strong>Total Appointments:</strong> <?= $total_appointments ?> &nbsp;|&nbsp;
       <strong>Total Doctors:</strong> <?= $total_doctors ?> &nbsp;|&nbsp;
       <strong>Total Patients:</strong> <?= $total_patients ?></p>

    <h3 style="margin-top:30px;">Appointments per Doctor</h3>
    <canvas id="doctorChart" height="100"></canvas>
</div>

<script>
const ctx = document.getElementById('doctorChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php
            $by_doctor->data_seek(0);
            $labels = [];
            while ($r = $by_doctor->fetch_assoc()) { $labels[] = "'" . addslashes($r['name']) . "'"; }
            echo implode(',', $labels);
        ?>],
        datasets: [{
            label: 'Appointments',
            data: [<?php
                $by_doctor->data_seek(0);
                $vals = [];
                while ($r = $by_doctor->fetch_assoc()) { $vals[] = $r['total']; }
                echo implode(',', $vals);
            ?>],
            backgroundColor: '#2c7a7b'
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>
