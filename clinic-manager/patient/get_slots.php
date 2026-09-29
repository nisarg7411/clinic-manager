<?php
require_once '../includes/config.php';
require_role('patient');
header('Content-Type: application/json');

$doctor_id = intval($_GET['doctor_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$day_name = date('l', strtotime($date)); // e.g. "Monday"

// Get doctor's availability window for that day of week
$stmt = $conn->prepare("SELECT start_time, end_time, slot_duration_minutes FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
$stmt->bind_param("is", $doctor_id, $day_name);
$stmt->execute();
$avail = $stmt->get_result()->fetch_assoc();

if (!$avail) {
    echo json_encode(['slots' => []]); // doctor doesn't work that day
    exit();
}

// Generate all slots between start and end time
$slots = [];
$start = strtotime($avail['start_time']);
$end = strtotime($avail['end_time']);
$duration = $avail['slot_duration_minutes'] * 60;

// Fetch already booked slots for that doctor+date
$booked_stmt = $conn->prepare("SELECT time_slot FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
$booked_stmt->bind_param("is", $doctor_id, $date);
$booked_stmt->execute();
$booked_result = $booked_stmt->get_result();
$booked_times = [];
while ($row = $booked_result->fetch_assoc()) {
    $booked_times[] = substr($row['time_slot'], 0, 5); // "HH:MM"
}

for ($time = $start; $time < $end; $time += $duration) {
    $time_str = date('H:i', $time);
    $slots[] = [
        'time' => $time_str,
        'booked' => in_array($time_str, $booked_times)
    ];
}

echo json_encode(['slots' => $slots]);
?>
