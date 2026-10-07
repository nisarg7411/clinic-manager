<?php
require_once '../includes/config.php';
require_role('patient');

$message = '';
$error = '';

/* Handle appointment cancellation */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {

    $appointment_id = intval($_POST['appointment_id']);

    if ($appointment_id > 0) {

        // Only allow the logged-in patient to cancel their own booked appointment
        $stmt = $conn->prepare("
            UPDATE appointments
            SET status = 'cancelled'
            WHERE id = ?
              AND patient_id = ?
              AND status = 'booked'
        ");

        $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Appointment cancelled successfully.";
        } else {
            $error = "Unable to cancel this appointment. It may already be completed or cancelled.";
        }

    } else {
        $error = "Invalid appointment.";
    }
}

/* Get patient's appointments */
$stmt = $conn->prepare("
    SELECT 
        a.id,
        u.name AS doctor_name,
        d.specialization,
        a.appointment_date,
        a.time_slot,
        a.status
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

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.cancel-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 7px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.cancel-btn:hover {
    background: #b02a37;
}

.success-message {
    background: #d1e7dd;
    color: #0f5132;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.error-message {
    background: #f8d7da;
    color: #842029;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.cancelled-badge {
    color: #842029;
}

.completed-badge {
    color: #0f5132;
}
</style>

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


    <?php if ($message): ?>

        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <table>

        <tr>
            <th>Doctor</th>
            <th>Specialization</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Prescription</th>
            <th>Action</th>
        </tr>


        <?php while ($row = $appointments->fetch_assoc()): ?>

        <tr>

            <td>
                <?= htmlspecialchars($row['doctor_name']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['specialization']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['appointment_date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($row['time_slot']) ?>
            </td>

            <td>

                <?php if ($row['status'] === 'cancelled'): ?>

                    <span class="badge cancelled">
                        Cancelled
                    </span>

                <?php elseif ($row['status'] === 'completed'): ?>

                    <span class="badge completed">
                        Completed
                    </span>

                <?php else: ?>

                    <span class="badge booked">
                        Booked
                    </span>

                <?php endif; ?>

            </td>


            <td>

                <a href="view_prescription.php?appointment_id=<?= $row['id'] ?>">
                    View
                </a>

            </td>


            <td>

                <?php if ($row['status'] === 'booked'): ?>

                    <form method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this appointment?');">

                        <input
                            type="hidden"
                            name="appointment_id"
                            value="<?= $row['id'] ?>"
                        >

                        <button
                            type="submit"
                            name="cancel_appointment"
                            class="cancel-btn"
                        >
                            Cancel Appointment
                        </button>

                    </form>

                <?php elseif ($row['status'] === 'cancelled'): ?>

                    <span>Cancelled</span>

                <?php elseif ($row['status'] === 'completed'): ?>

                    <span>Completed</span>

                <?php endif; ?>

            </td>

        </tr>

        <?php endwhile; ?>

    </table>


    <?php if ($appointments->num_rows === 0): ?>

        <p>
            No appointments yet.
            <a href="book_appointment.php">Book one now</a>.
        </p>

    <?php endif; ?>

</div>

</body>
</html>
