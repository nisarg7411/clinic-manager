<?php
require_once '../includes/config.php';
require_role('patient');

// Handle final booking submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $date = $_POST['appointment_date'];
    $slot = $_POST['time_slot'];

    // SERVER-SIDE conflict check (never trust JS alone)
    $check = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND time_slot = ? AND status != 'cancelled'");
    $check->bind_param("iss", $doctor_id, $date, $slot);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<p class='error'>Sorry, that slot was just booked by someone else. Please pick another.</p>";
    } else {
        $insert = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, time_slot) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiss", $_SESSION['user_id'], $doctor_id, $date, $slot);
        if ($insert->execute()) {
            header("Location: dashboard.php?booked=1");
            exit();
        } else {
            $message = "<p class='error'>Booking failed. Slot may have just been taken.</p>";
        }
    }
}

$doctors = $conn->query("SELECT d.id, u.name, d.specialization FROM doctors d JOIN users u ON d.user_id = u.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <strong>🏥 Clinic Manager</strong>
    <div><a href="dashboard.php">My Dashboard</a> <a href="../logout.php">Logout</a></div>
</div>

<div class="container">
    <h2>Book an Appointment</h2>
    <?= $message ?>

    <div class="form-group">
        <label>Select Doctor *</label>
        <select id="doctor_id" onchange="loadSlots()">
            <option value="">-- Choose Doctor --</option>
            <?php while ($doc = $doctors->fetch_assoc()): ?>
                <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)</option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Select Date *</label>
        <input type="date" id="appointment_date" min="<?= date('Y-m-d') ?>" onchange="loadSlots()">
    </div>

    <div id="slotsContainer"></div>

    <form method="POST" id="bookingForm" style="margin-top:20px; display:none;">
        <input type="hidden" name="doctor_id" id="hidden_doctor_id">
        <input type="hidden" name="appointment_date" id="hidden_date">
        <input type="hidden" name="time_slot" id="hidden_slot">
        <input type="hidden" name="confirm_booking" value="1">
        <p>Selected slot: <strong id="selectedSlotText"></strong></p>
        <button type="submit">Confirm Booking</button>
    </form>
</div>

<script>
let selectedSlot = null;

function loadSlots() {
    const doctorId = document.getElementById('doctor_id').value;
    const date = document.getElementById('appointment_date').value;
    const container = document.getElementById('slotsContainer');
    document.getElementById('bookingForm').style.display = 'none';
    selectedSlot = null;

    if (!doctorId || !date) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = '<p>Loading slots...</p>';

    // AJAX call to PHP endpoint to fetch real-time slot availability
    fetch(`get_slots.php?doctor_id=${doctorId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = `<p class="error">${data.error}</p>`;
                return;
            }
            if (data.slots.length === 0) {
                container.innerHTML = '<p>No slots available for this day.</p>';
                return;
            }
            let html = '<label>Available Time Slots *</label><div class="slot-grid">';
            data.slots.forEach(s => {
                const cls = s.booked ? 'slot booked' : 'slot';
                html += `<div class="${cls}" ${s.booked ? '' : `onclick="selectSlot('${s.time}', this)"`}>${s.time}</div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p class="error">Could not load slots. Try again.</p>';
        });
}

function selectSlot(time, el) {
    document.querySelectorAll('.slot').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    selectedSlot = time;

    document.getElementById('hidden_doctor_id').value = document.getElementById('doctor_id').value;
    document.getElementById('hidden_date').value = document.getElementById('appointment_date').value;
    document.getElementById('hidden_slot').value = time;
    document.getElementById('selectedSlotText').innerText =
        document.getElementById('appointment_date').value + ' at ' + time;
    document.getElementById('bookingForm').style.display = 'block';
}
</script>
</body>
</html>
