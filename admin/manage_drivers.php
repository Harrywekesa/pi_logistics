<?php
session_start();
require 'db.php'; // Include database connection file

// Ensure only admin can access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Add driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_driver'])) {
    $name = $_POST['name'];
    $license_number = $_POST['license_number'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $tuktuk_assigned = !empty($_POST['tuktuk_assigned']) ? $_POST['tuktuk_assigned'] : NULL; // Ensure NULL if not assigned

    // Check if tuktuk is already assigned
    if ($tuktuk_assigned) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM drivers WHERE tuktuk_assigned = ?");
        $stmt->execute([$tuktuk_assigned]);
        $tuktuk_in_use = $stmt->fetchColumn();

        if ($tuktuk_in_use > 0) {
            echo "Error: This tuktuk is already assigned to another driver.";
        } else {
            // Prepare and execute the insert query with error handling
            try {
                $stmt = $conn->prepare("INSERT INTO drivers (name, license_number, email, phone, tuktuk_assigned) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $license_number, $email, $phone, $tuktuk_assigned]);
                echo "Driver added successfully!";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        // Insert driver without assigning a tuktuk
        try {
            $stmt = $conn->prepare("INSERT INTO drivers (name, license_number, email, phone, tuktuk_assigned) VALUES (?, ?, ?, ?, NULL)");
            $stmt->execute([$name, $license_number, $email, $phone]);
            echo "Driver added successfully!";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Suspend driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_driver'])) {
    $driver_id = $_POST['driver_id'];
    $stmt = $conn->prepare("UPDATE drivers SET status = 'suspended' WHERE driver_id = ?");
    $stmt->execute([$driver_id]);
}

// Unsuspend driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unsuspend_driver'])) {
    $driver_id = $_POST['driver_id'];
    $stmt = $conn->prepare("UPDATE drivers SET status = 'active' WHERE driver_id = ?");
    $stmt->execute([$driver_id]);
}

// Remove driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_driver'])) {
    $driver_id = $_POST['driver_id'];
    $stmt = $conn->prepare("DELETE FROM drivers WHERE driver_id = ?");
    $stmt->execute([$driver_id]);
}

// Fetch all drivers
$stmt = $conn->query("SELECT * FROM drivers");
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tuktuks
$stmt = $conn->prepare("SELECT * FROM tuktuks");
$stmt->execute();
$tuktuks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Drivers</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Link to external CSS file -->
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="admin.php">Pi Logistics</a>
        </div>
        <ul class="nav-links">
            <li><a href="admin.php">Admin Dashboard</a></li>
            <li><a href="manage_cashiers.php">Manage Cashiers</a></li>
            <li><a href="manage_tuktuks.php">Manage Tuktuks</a></li>
            <li><a href="manage_drivers.php">Manage Drivers</a></li>
            <li><a href="logout.php" class="login-btn">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="section">
    <h1>Manage Drivers</h1>

    <!-- Add Driver Form -->
    <h2>Add Driver</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="license_number" placeholder="License Number" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <!-- Dropdown for Tuktuk assignment -->
        <select name="tuktuk_assigned">
            <option value="">Select Tuktuk</option>
            <?php foreach ($tuktuks as $tuktuk): ?>
                <option value="<?= htmlspecialchars($tuktuk['tuktuk_id']) ?>"><?= htmlspecialchars($tuktuk['type']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_driver" class="btn-add">Add Driver</button>
    </form>
</div>

<!-- Drivers List -->
<div class="section">
    <h2>Drivers List</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Assigned Tuktuk</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($drivers as $driver): ?>
            <tr>
                <td><?= htmlspecialchars($driver['driver_id']) ?></td>
                <td><?= htmlspecialchars($driver['name']) ?></td>
                <td><?= htmlspecialchars($driver['email']) ?></td>
                <td><?= htmlspecialchars($driver['phone']) ?></td>
                <td><?= htmlspecialchars($driver['tuktuk_assigned']) ?></td>
                <td><?= htmlspecialchars($driver['status']) ?></td>
                <td>
                    <?php if ($driver['status'] == 'active'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['driver_id']) ?>">
                            <button type="submit" name="suspend_driver">Suspend</button>
                        </form>
                    <?php elseif ($driver['status'] == 'suspended'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['driver_id']) ?>">
                            <button type="submit" name="unsuspend_driver">Unsuspend</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['driver_id']) ?>">
                            <button type="submit" name="remove_driver">Remove</button>
                        </form>
                    <?php else: ?>
                        Suspended
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Generate Driver Report -->
<div class="section">
    <h2>Generate Report for Driver</h2>
    <form method="GET" action="generate_report.php">
        <select name="driver_id" required>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?= htmlspecialchars($driver['driver_id']) ?>"><?= htmlspecialchars($driver['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Generate Report</button>
    </form>
</div>

</body>
<footer>
    <p>&copy; 2024 Pi Logistics. All rights reserved.</p>
</footer>
</html>
