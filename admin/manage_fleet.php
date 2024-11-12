<?php
// manage_fleet.php
require 'db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Add new Tuk Tuk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    $vehicle_type = $_POST['vehicle_type'];
    $registration_number = $_POST['registration_number'];
    $capacity = $_POST['capacity'];
    $mileage = $_POST['mileage'];
    $driver_id = $_POST['driver_id'];

    try {
        // Insert vehicle data into the fleet table
        $stmt = $conn->prepare("INSERT INTO fleet (vehicle_type, registration_number, capacity, mileage, driver_id) 
                                VALUES (:vehicle_type, :registration_number, :capacity, :mileage, :driver_id)");
        
        // Bind parameters
        $stmt->bindParam(':vehicle_type', $vehicle_type);
        $stmt->bindParam(':registration_number', $registration_number);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':mileage', $mileage);
        $stmt->bindParam(':driver_id', $driver_id);
        
        // Execute query
        $stmt->execute();
        echo "Vehicle added successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Suspend Tuk Tuk for maintenance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_vehicle'])) {
    $registration_number = $_POST['registration_number'];
    try {
        $stmt = $conn->prepare("UPDATE fleet SET is_active = FALSE WHERE registration_number = :registration_number");
        $stmt->bindParam(':registration_number', $registration_number);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch fleet and drivers
try {
    // Fetch fleet with driver names included by joining with drivers table
    $stmt = $conn->prepare("SELECT fleet.*, drivers.name AS driver_name FROM fleet 
                            LEFT JOIN drivers ON fleet.driver_id = drivers.id");
    $stmt->execute();
    $fleet = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch drivers for the dropdown
    $stmt = $conn->prepare("SELECT id, name FROM drivers");
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Fleet</title>
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
    <h1>Manage Fleet</h1>
    <!-- Add Tuk Tuk Form -->
    <form method="POST" class="form-group">
        <select name="vehicle_type" required>
            <option value="">Select Tuk Tuk Type</option>
            <option value="Loader">Loader</option>
            <option value="Tipper">Tipper</option>
            <option value="Cabin">Cabin</option>
        </select>
        <input type="text" name="registration_number" placeholder="Registration Number" required>
        <input type="number" name="capacity" placeholder="Capacity (kg)" required>
        <input type="number" name="mileage" placeholder="Mileage" required>
        <select name="driver_id" required>
            <option value="">Select Driver</option>
            <?php foreach ($drivers as $driver): ?>
                <option value="<?= $driver['id'] ?>"><?= $driver['name'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="add_vehicle" class="btn-add">Add Tuk Tuk</button>
    </form>
</div>

<!-- Fleet List -->
<div class="section">
    <h2>Fleet List</h2>
    <table>
        <tr><th>Type</th><th>Registration No.</th><th>Capacity</th><th>Mileage</th><th>Driver</th><th>Actions</th></tr>
        <?php foreach ($fleet as $vehicle): ?>
        <tr>
            <td><?= $vehicle['vehicle_type'] ?></td>
            <td><?= $vehicle['registration_number'] ?></td>
            <td><?= $vehicle['capacity'] ?> kg</td>
            <td><?= $vehicle['mileage'] ?> km</td>
            <td><?= $vehicle['driver_name'] ?></td>
            <td>
                <?php if ($vehicle['is_active']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="registration_number" value="<?= $vehicle['registration_number'] ?>">
                        <button type="submit" name="suspend_vehicle">Suspend for Maintenance</button>
                    </form>
                <?php else: ?>
                    Suspended
                <?php endif; ?>
                <form method="POST" action="generate_report.php" style="display:inline;">
                    <input type="hidden" name="registration_number" value="<?= $vehicle['registration_number'] ?>">
                    <button type="submit" name="generate_report">Generate Report</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
