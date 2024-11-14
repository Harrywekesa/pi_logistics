<?php
// cashier.php
session_start();
require 'db.php';

// Redirect if user not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Use session user ID as cashier ID for reference
$cashier_id = $_SESSION['cashier_id'];

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle client search by ID number
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_client'])) {
    if (!empty($_POST['national_id'])) {
        $national_id = $_POST['national_id'];
        $stmt = $conn->prepare("SELECT * FROM clients WHERE national_id = :national_id");
        $stmt->bindParam(':national_id', $national_id, PDO::PARAM_STR);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            echo "Client found: " . htmlspecialchars($client['name']);
        } else {
            echo "Client not found. You may add a new client.";
        }
    } else {
        echo "Error: Please provide a valid ID number.";
    }
}

// Handle new client registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_client'])) {
    if (!empty($_POST['name']) && !empty($_POST['national_id'])  && !empty($_POST['contact_number']) && !empty($_POST['email']) && !empty($_POST['address'])) {
        $name = $_POST['name'];
        $national_id = $_POST['national_id'];
        $contact_number = $_POST['contact_number'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("INSERT INTO clients (name, national_id, contact_number, email, address) VALUES (:name, :national_id, :contact_number, :email, :address)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':national_id', $national_id, PDO::PARAM_STR);
        $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->execute();
        echo "New client added successfully!";
    } else {
        echo "Error: Please provide all client details.";
    }
}

// Handle booking creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_booking'])) {
    if (!empty($_POST['client_id']) && !empty($_POST['cargo_description']) && !empty($_POST['weight']) && !empty($_POST['source']) && !empty($_POST['destination']) && !empty($_POST['distance']) && !empty($_POST['tuktuk_id'])) {
        $client_id = $_POST['client_id'];
        $cargo_description = $_POST['cargo_description'];
        $weight = $_POST['weight'];
        $source = $_POST['source'];
        $destination = $_POST['destination'];
        $distance = $_POST['distance'];
        $tuktuk_id = $_POST['tuktuk_id'];

        // Calculate cost (example: distance * weight * price_per_unit)
        $price_per_unit = 10; // Example price per unit
        $cost = $distance * $weight * $price_per_unit;

        $stmt = $conn->prepare("INSERT INTO bookings (client_id, cargo_description, source, destination, distance, weight, cost, pickup, tuktuk_id, status, date_of_transport) 
                                VALUES (:client_id, :cargo_description, :source, :destination, :distance, :weight, :cost, :pickup, :tuktuk_id, 'Pending', CURDATE())");
        $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':cargo_description', $cargo_description, PDO::PARAM_STR);
        $stmt->bindParam(':source', $source, PDO::PARAM_STR);
        $stmt->bindParam(':destination', $destination, PDO::PARAM_STR);
        $stmt->bindParam(':distance', $distance, PDO::PARAM_STR);
        $stmt->bindParam(':weight', $weight, PDO::PARAM_STR);
        $stmt->bindParam(':cost', $cost, PDO::PARAM_STR);
        $stmt->bindParam(':pickup', $source, PDO::PARAM_STR);
        $stmt->bindParam(':tuktuk_id', $tuktuk_id, PDO::PARAM_INT);
        $stmt->execute();
        echo "Booking created successfully!";
    } else {
        echo "Error: Missing booking details.";
    }
}

// Handle booking status update (for completing or canceling bookings)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_booking'])) {
    if (!empty($_POST['booking_id']) && !empty($_POST['new_status'])) {
        $booking_id = $_POST['booking_id'];
        $new_status = $_POST['new_status'];

        $stmt = $conn->prepare("UPDATE bookings SET status = :status WHERE booking_id = :booking_id");
        $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        echo "Booking status updated successfully!";
    } else {
        echo "Error: Missing booking status details.";
    }
}

// Fetch all previous bookings regardless of status
$stmt_all = $conn->prepare("SELECT * FROM bookings");
$stmt_all->execute();
$all_bookings = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

// Fetch current bookings and available tuktuks
$stmt = $conn->prepare("SELECT * FROM bookings WHERE status = 'Pending' OR status = 'In Progress'");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_tuktuk = $conn->prepare("SELECT * FROM tuktuks WHERE is_active = 1"); // Use 'is_active' instead of 'status'
$stmt_tuktuk->execute();
$tuktuks = $stmt_tuktuk->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo"><a href="index.php">Pi Logistics</a></div>
        <ul class="nav-links">
            <li><a href="cashier.php">Cashier's Dashboard</a></li>
            <li><a href="logout.php" class="login-btn">Logout</a></li>
        </ul>
    </nav>
</header>
<div class="section">
    <h1>Cashier Dashboard</h1>
    <h2>Create New Booking</h2>
    
    <!-- Search Client Form -->
    <form method="POST">
        <input type="text" name="national_id" placeholder="Enter National ID to Search for Client" required>
        <button type="submit" name="search_client">Search Client</button>
    </form>

    <!-- Add New Client Form -->
    <form method="POST">
        <h3>Add New Client</h3>
        <input type="text" name="name" placeholder="Client Name" required>
        <input type="text" name="national_id" placeholder="National ID" required>
        <input type="text" name="contact_number" placeholder="Contact Number" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <button type="submit" name="add_client">Add Client</button>
    </form>

    <!-- Booking Creation Form -->
    <form method="POST">
        <h3>Booking Details</h3>
        <input type="number" name="client_id" placeholder="Customer ID" required>
        <input type="text" name="cargo_description" placeholder="Cargo Description" required>
        <input type="number" name="weight" placeholder="Weight (kg)" required>
        <input type="text" name="source" placeholder="Source Location" required>
        <input type="text" name="destination" placeholder="Destination Location" required>
        <input type="number" name="distance" placeholder="Distance (km)" required>

        <select name="tuktuk_id" required>
            <option value="">Select Tuktuk</option>
            <?php foreach ($tuktuks as $tuktuk): ?>
                <option value="<?= htmlspecialchars($tuktuk['tuktuk_id']) ?>"><?= htmlspecialchars($tuktuk['registration_number']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="create_booking">Create Booking</button>
    </form>
</div>

<div class="section">
    <h2>Current Bookings</h2>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Customer ID</th>
            <!-- <th>Customer Name</th> -->
            <th>Cargo Description</th>
            <th>Weight</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Distance</th>
            <th>Cost</th>
            <th>Tuktuk Assigned</th>
            <th>Status</th>
            <th>Actions</th>
            <th>Print</th>
        </tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
            <td><?= htmlspecialchars($booking['client_id']) ?></td>
            <td><?= htmlspecialchars($booking['cargo_description']) ?></td>
            <td><?= htmlspecialchars($booking['weight']) ?></td>
            <td><?= htmlspecialchars($booking['source']) ?></td>
            <td><?= htmlspecialchars($booking['destination']) ?></td>
            <td><?= htmlspecialchars($booking['distance']) ?></td>
            <td><?= htmlspecialchars($booking['cost']) ?></td>
            <td><?= htmlspecialchars($booking['tuktuk_id']) ?></td>
            <td><?= htmlspecialchars($booking['status']) ?></td>
            <td>
                <?php if ($booking['status'] == 'Pending'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                        <input type="hidden" name="new_status" value="In Progress">
                        <button type="submit" name="update_booking">Start</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                        <input type="hidden" name="new_status" value="Cancelled">
                        <button type="submit" name="update_booking">Cancel</button>
                    </form>
                <?php elseif ($booking['status'] == 'In Progress'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                        <input type="hidden" name="new_status" value="Completed">
                        <button type="submit" name="update_booking">Complete</button>
                    </form>
                <?php else: ?>
                    <?= htmlspecialchars($booking['status']) ?>
                <?php endif; ?>
                
            </td>
            <td>
                    <!-- New Print Buttons -->
    <form method="POST" action="print_receipt.php" target="_blank" style="display:inline;">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
        <button type="submit">Print Receipt</button>
    </form>
    <form method="POST" action="print_invoice.php" target="_blank" style="display:inline;">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
        <button type="submit">Print Invoice</button>
    </form>
    <form method="POST" action="print_delivery_note.php" target="_blank" style="display:inline;">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
        <button type="submit">Print Delivery Note</button>
    </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<div class="section">
    <h2>All Previous Bookings</h2>
    <table>
        <tr>
            <th>Booking ID</th>
            <th>Customer ID</th>
            <th>Cargo Description</th>
            <th>Weight</th>
            <th>Source</th>
            <th>Destination</th>
            <th>Distance</th>
            <th>Cost</th>
            <th>Tuktuk</th>
            <th>Status</th>
            <th>Date of Transport</th>
            <th>Created At</th>
        </tr>
        <?php foreach ($all_bookings as $booking): ?>
        <tr>
            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
            <td><?= htmlspecialchars($booking['client_id']) ?></td>
            <td><?= htmlspecialchars($booking['cargo_description']) ?></td>
            <td><?= htmlspecialchars($booking['weight']) ?></td>
            <td><?= htmlspecialchars($booking['source']) ?></td>
            <td><?= htmlspecialchars($booking['destination']) ?></td>
            <td><?= htmlspecialchars($booking['distance']) ?></td>
            <td><?= htmlspecialchars($booking['cost']) ?></td>
            <td><?= htmlspecialchars($booking['tuktuk_id']) ?></td>
            <td><?= htmlspecialchars($booking['status']) ?></td>
            <td><?= htmlspecialchars($booking['date_of_transport']) ?></td>
            <td><?= htmlspecialchars($booking['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
<footer>
    <p>&copy; <?= date("Y"); ?> Pi Logistics. All rights reserved.</p>
</footer>
</html>
