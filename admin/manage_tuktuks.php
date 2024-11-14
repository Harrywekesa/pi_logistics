<?php
// manage_tuktuks.php
require 'db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Add new Tuk Tuk with driver assignment check
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tuktuk'])) {
    $registration_number = $_POST['registration_number'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $mileage = $_POST['mileage'];
    $driver_id = !empty($_POST['driver_id']) ? $_POST['driver_id'] : NULL;
    $notes = $_POST['notes'];

    try {
        // Check if the driver is already assigned to another tuk tuk
        $stmt = $conn->prepare("SELECT * FROM tuktuks WHERE driver_id = :driver_id AND is_active = TRUE");
        $stmt->bindParam(':driver_id', $driver_id);
        $stmt->execute();
        $existing_driver = $stmt->fetch();

        if ($existing_driver) {
            echo "Driver is already assigned to another Tuk Tuk!";
        } else {
            // Insert vehicle data into the tuktuks table
            $stmt = $conn->prepare("INSERT INTO tuktuks (type, registration_number, capacity, mileage, driver_id, notes) 
                                    VALUES (:type, :registration_number, :capacity, :mileage, :driver_id, :notes)");
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':registration_number', $registration_number);
            $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
            $stmt->bindParam(':mileage', $mileage, PDO::PARAM_INT);
            $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
            $stmt->bindParam(':notes', $notes);
            $stmt->execute();

            // Update the driver’s assigned Tuk Tuk in the drivers table
            if ($driver_id) {
                $stmt = $conn->prepare("UPDATE drivers SET tuktuk_assigned = :tuktuk_id WHERE driver_id = :driver_id");
                $tuktuk_id = $conn->lastInsertId(); // Retrieve the ID of the newly inserted Tuk Tuk
                $stmt->bindParam(':tuktuk_id', $tuktuk_id, PDO::PARAM_INT);
                $stmt->bindParam(':driver_id', $driver_id, PDO::PARAM_INT);
                $stmt->execute();
            }

            echo "Tuk Tuk added and assigned to driver successfully!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
// Suspend Tuk Tuk for maintenance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_vehicle'])) {
    $registration_number = $_POST['registration_number'];
    try {
        $stmt = $conn->prepare("UPDATE tuktuks SET is_active = FALSE WHERE registration_number = :registration_number");
        $stmt->bindParam(':registration_number', $registration_number);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Unsuspend Tuk Tuk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unsuspend_vehicle'])) {
    $registration_number = $_POST['registration_number'];
    try {
        $stmt = $conn->prepare("UPDATE tuktuks SET is_active = TRUE WHERE registration_number = :registration_number");
        $stmt->bindParam(':registration_number', $registration_number);
        $stmt->execute();
        echo "Tuk Tuk unsuspended successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch tuktuks and drivers
try {
    // Fetch tuktuks with driver names included by joining with drivers table
    $stmt = $conn->prepare("SELECT tuktuks.*, drivers.name AS driver_name FROM tuktuks 
                            LEFT JOIN drivers ON tuktuks.driver_id = drivers.driver_id");
    $stmt->execute();
    $tuktuks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch drivers for the dropdown
    $stmt = $conn->prepare("SELECT driver_id, name FROM drivers");
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Tuktuks</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Link to external CSS file -->
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">Pi Logistics</a>
        </div>
        <ul class="nav-links">
            <li><a href="admin.php">Admin Dashboard</a></li>
            <li><a href="manage_cashiers.php">Manage Cashiers</a></li>
            <li><a href="manage_drivers.php">Manage Drivers</a></li>
            <li><a href="../logout.php" class="login-btn">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="section">
    <h1>Manage Tuktuks</h1>
    <!-- Add Tuk Tuk Form -->
    <form method="POST" class="form-group">
        <select name="type" required>
            <option value="">Select Tuk Tuk Type</option>
            <option value="Loader">Loader</option>
            <option value="Tipper">Tipper</option>
            <option value="Cabin">Cabin</option>
        </select>
        <input type="text" name="registration_number" placeholder="Registration Number" required>
        <input type="number" name="capacity" placeholder="Capacity (kg)" required>
        <input type="number" name="mileage" placeholder="Mileage" required>
        <input type="text" name="notes" placeholder="Notes about the tuktuk" required>
        <select name="driver_id" required>
            <option value="">Select Driver</option>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?= htmlspecialchars($driver['driver_id']) ?>"><?= htmlspecialchars($driver['name']) ?></option> <!-- Corrected driver_id -->
            <?php endforeach; ?>
        </select>

        <button type="submit" name="add_tuktuk" class="btn-add">Add Tuk Tuk</button> <!-- Corrected name to add_tuktuk -->
    </form>
</div>

<!-- Tuktuks List -->
<div class="section">
    <h2>Tuktuks List</h2>
    <table>
        <tr>
            <th>Type</th><th>Registration No.</th><th>Capacity</th><th>Mileage</th><th>Driver</th><th>Status</th><th>Actions</th>
        </tr>
        <?php foreach ($tuktuks as $vehicle): ?>
        <tr>
            <td><?= htmlspecialchars($vehicle['type']) ?></td>
            <td><?= htmlspecialchars($vehicle['registration_number']) ?></td>
            <td><?= htmlspecialchars($vehicle['capacity']) ?> kg</td>
            <td><?= htmlspecialchars($vehicle['mileage']) ?> km</td>
            <td><?= htmlspecialchars($vehicle['driver_name']) ?></td>
            <td>
                <?php if ($vehicle['is_active'] == 1): ?>
                    <span>Active</span>
                <?php else: ?>
                    <span>Suspended</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($vehicle['is_active'] == 1): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="registration_number" value="<?= htmlspecialchars($vehicle['registration_number']) ?>">
                        <button type="submit" name="suspend_vehicle">Suspend</button>
                    </form>
                <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="registration_number" value="<?= htmlspecialchars($vehicle['registration_number']) ?>">
                        <button type="submit" name="unsuspend_vehicle">Unsuspend</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="generate_report.php" style="display:inline;">
                    <input type="hidden" name="registration_number" value="<?= htmlspecialchars($vehicle['registration_number']) ?>">
                    <button type="submit" name="generate_report">Generate Report</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
<footer>
    <p>&copy; 2024 Pi Logistics. All rights reserved.</p>
</footer>
</html>
