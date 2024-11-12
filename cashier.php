<?php
// cashier.php
require 'db.php';
session_start();

// Check if the user is logged in and is not an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['is_admin'] === true) {
    header("Location: login.php");
    exit;
}

// Get the cashier ID from the session
$cashier_id = $_SESSION['cashier_id'];

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Handle booking creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_booking'])) {
    $customer_id = $_POST['customer_id'];
    $cargo_type = $_POST['cargo_type'];
    $source_location = $_POST['source_location'];
    $destination_location = $_POST['destination_location'];

    // Insert new booking into the bookings table
    $stmt = $conn->prepare("INSERT INTO bookings (customer_id, cargo_type, source_location, destination_location, status, cashier_id) VALUES (?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("isssi", $customer_id, $cargo_type, $source_location, $destination_location, $cashier_id);
    $stmt->execute();
    $stmt->close();
}

// Handle booking update (start or complete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch current bookings assigned to the cashier
$bookings = $conn->query("SELECT * FROM bookings WHERE cashier_id = $cashier_id")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cashier Dashboard</title>
</head>
<body>
    <h1>Cashier Dashboard</h1>
    
    <h2>Create New Booking</h2>
    <form method="POST">
        <input type="text" name="customer_id" placeholder="Customer ID" required>
        <input type="text" name="cargo_type" placeholder="Cargo Type" required>
        <input type="text" name="source_location" placeholder="Source Location" required>
        <input type="text" name="destination_location" placeholder="Destination Location" required>
        <button type="submit" name="create_booking">Create Booking</button>
    </form>

    <h2>Current Bookings</h2>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Customer ID</th>
            <th>Cargo Type</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?= htmlspecialchars($booking['id']) ?></td>
            <td><?= htmlspecialchars($booking['customer_id']) ?></td>
            <td><?= htmlspecialchars($booking['cargo_type']) ?></td>
            <td><?= htmlspecialchars($booking['source_location']) ?></td>
            <td><?= htmlspecialchars($booking['destination_location']) ?></td>
            <td><?= htmlspecialchars($booking['status']) ?></td>
            <td>
                <?php if ($booking['status'] == 'Pending'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                        <input type="hidden" name="new_status" value="In Progress">
                        <button type="submit" name="update_booking">Start Booking</button>
                    </form>
                <?php elseif ($booking['status'] == 'In Progress'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['id']) ?>">
                        <input type="hidden" name="new_status" value="Completed">
                        <button type="submit" name="update_booking">Complete Booking</button>
                    </form>
                <?php else: ?>
                    Completed
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
