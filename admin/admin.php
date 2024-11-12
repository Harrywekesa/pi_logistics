<?php
// admin.php
session_start();
require 'db.php';

// Ensure only admin can access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_cashier'])) {
        // Register a new cashier
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $cashier_id = uniqid('CASH_');
        $bank_account = $_POST['bank_account'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("INSERT INTO cashiers (username, password, cashier_id, bank_account, phone, email, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'email' => $cashier_id,
            'bank_account' => $bank_account,
            'phone' => $phone,
            'email' => $email,
            'is_active' => $is_active
        ]);
        $success_message = "Cashier $username successfully registered!";

    } elseif (isset($_POST['manage_tuktuk'])) {
        // Add a new tuktuk to fleet
        $type = $_POST['type'];
        $number_plate = $_POST['number_plate'];
        $assigned_driver = $_POST['assigned_driver'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("INSERT INTO tuktuks (type, number_plate, assigned_driver, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'type' => $type,
            'number_plate' => $number_plate,
            'assigned_driver' => $assigned_driver,
            'description' => $description
        ]);
        
    } elseif (isset($_POST['suspend_cashier'])) {
        // Suspend a cashier
        $cashier_id = $_POST['cashier_id'];  // Get cashier ID from form input
        $stmt = $conn->prepare("UPDATE cashiers SET is_active = 0 WHERE cashier_id = ?");
        $stmt->execute([
        'cashier_id' => $cashier_id    
        ]);
        }
}

/// Fetch cashiers
$stmt = $conn->prepare("SELECT * FROM cashiers");
$stmt->execute();
$cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tuktuks
$stmt = $conn->prepare("SELECT * FROM tuktuks");
$stmt->execute();
$tuktuks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
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
                <li><a href="manage_fleet.php">Manage Fleet</a></li>
                <li><a href="manage_drivers.php">Manage Drivers</a></li>
                <li><a href="login.php" class="login-btn">Login</a></li>
            </ul>
        </nav>
    </header>
    <h1>Admin Dashboard</h1>
    <div class="section">
    <h2>Manage Cashiers</h2>
    <form method="POST" class="form-group">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="bank_account" placeholder="Bank Account">
        <input type="text" name="phone" placeholder="Phone">
        <input type="email" name="email" placeholder="Email">
        <button type="submit" name="add_cashier" class="btn btn-add">Add Cashier</button>
    </form>
</div>

    <h2>Cashiers List</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>
        <?php foreach ($cashiers as $cashier): ?>
        <tr>
            <td><?= htmlspecialchars($cashier['cashier_id']) ?></td>
            <td><?= htmlspecialchars($cashier['username']) ?></td>
            <td><?= htmlspecialchars($cashier['email']) ?></td>
            <td>
                <?php if ($cashier['is_active']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="cashier_id" value="<?= htmlspecialchars($cashier['cashier_id']) ?>">
                        <button type="submit" name="suspend_cashier">Suspend</button>
                    </form>
                <?php else: ?>
                    Suspended
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Manage Fleet</h2>
    <form method="POST">
        <select name="type" required>
            <option value="Tuk Tuk Loader">Tuk Tuk Loader</option>
            <option value="Tuk Tuk Tipper">Tuk Tuk Tipper</option>
            <option value="Tuk Tuk Cabin">Tuk Tuk Cabin</option>
        </select>
        <input type="text" name="number_plate" placeholder="Number Plate" required>
        <input type="text" name="assigned_driver" placeholder="Assigned Driver">
        <textarea name="description" placeholder="Description"></textarea>
        <button type="submit" name="manage_tuktuk">Add Tuk Tuk</button>
    </form>

    <h2>Tuktuks List</h2>
    <table>
        <tr><th>Type</th><th>Number Plate</th><th>Driver</th></tr>
        <?php foreach ($tuktuks as $tuktuk): ?>
        <tr>
            <td><?= htmlspecialchars($tuktuk['type']) ?></td>
            <td><?= htmlspecialchars($tuktuk['number_plate']) ?></td>
            <td><?= htmlspecialchars($tuktuk['assigned_driver']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
