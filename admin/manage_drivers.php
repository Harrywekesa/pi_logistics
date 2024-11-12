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
    $national_id = $_POST['national_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $assigned_tuktuk = $_POST['assigned_tuktuk'];

    $stmt = $conn->prepare("INSERT INTO drivers (name, national_id, email, phone, assigned_tuktuk) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $national_id, $email, $phone, $assigned_tuktuk]);
}

// Suspend driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_driver'])) {
    $driver_id = $_POST['driver_id'];

    $stmt = $conn->prepare("UPDATE drivers SET status = 'suspended' WHERE id = ?");
    $stmt->execute([$driver_id]);
}

// Remove driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_driver'])) {
    $driver_id = $_POST['driver_id'];

    $stmt = $conn->prepare("DELETE FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
}

// Fetch all drivers
$stmt = $conn->query("SELECT * FROM drivers");
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <a href="admin.php.php">Pi Logistics</a>
            </div>
            <ul class="nav-links">
                <li><a href="admin.php">Admin Dashboard</a></li>
                <li><a href="manage_cashiers.php">Manage Cashiers</a></li>
                <li><a href="manage_fleet.php">Manage Fleet</a></li>
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
        <input type="text" name="national_id" placeholder="National ID" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="text" name="assigned_tuktuk" placeholder="Assigned Tuktuk">
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
            <td><?= htmlspecialchars($driver['id']) ?></td>
            <td><?= htmlspecialchars($driver['name']) ?></td>
            <td><?= htmlspecialchars($driver['email']) ?></td>
            <td><?= htmlspecialchars($driver['phone']) ?></td>
            <td><?= htmlspecialchars($driver['assigned_tuktuk']) ?></td>
            <td><?= htmlspecialchars($driver['status']) ?></td>
            <td>
                <?php if ($driver['status'] == 'active'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
                        <button type="submit" name="suspend_driver">Suspend</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
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
                <option value="<?= htmlspecialchars($driver['id']) ?>"><?= htmlspecialchars($driver['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Generate Report</button>
    </form>
    </div>
</body>
</html>
