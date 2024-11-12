<?php
// manage_cashiers.php
require 'db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Add new cashier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_cashier'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $cashier_id = $_POST['cashier_id'];
    $bank_account = $_POST['bank_account'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    try {
        // Prepare the SQL query to insert data into the cashiers table
        $stmt = $conn->prepare("INSERT INTO cashiers (username, password, cashier_id, bank_account, phone, email) 
                                VALUES (:username, :password, :cashier_id, :bank_account, :phone, :email)");
        
        // Bind the parameters to the placeholders in the SQL query
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':cashier_id', $cashier_id);
        $stmt->bindParam(':bank_account', $bank_account);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);

        // Execute the query
        $stmt->execute();

        // You can also show a success message after insertion
        echo "Cashier added successfully!";
    } catch (PDOException $e) {
        // If there is an error, display the message
        echo "Error: " . $e->getMessage();
    }
}

// Suspend cashier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['suspend_cashier'])) {
    $cashier_id = $_POST['cashier_id'];
    try {
        $stmt = $conn->prepare("UPDATE cashiers SET is_active = FALSE WHERE cashier_id = :cashier_id");
        $stmt->bindParam(':cashier_id', $cashier_id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch cashiers
try {
    $stmt = $conn->prepare("SELECT * FROM cashiers");
    $stmt->execute();
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Cashiers</title>
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
            <li><a href="manage_fleet.php">Manage Fleet</a></li>
            <li><a href="manage_drivers.php">Manage Drivers</a></li>
            <li><a href="../logout.php" class="login-btn">Logout</a></li>
        </ul>
    </nav>
</header>

<div class = "section">
<h1>Manage Cashiers</h1>
<!-- Add Cashier Form -->
<form method="POST" class="form-group">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="bank_account" placeholder="Bank Account">
    <input type="number" name="cashier_id" placeholder="cashier_id" required>
    <input type="text" name="phone" placeholder="Phone">
    <input type="email" name="email" placeholder="Email">
    <button type="submit" name="add_cashier" class="btn-add">Add Cashier</button>
</form>
</div>
<!-- Cashiers List -->
<div class = "section">
<h2>Cashiers List</h2>
<table>
    <tr><th>ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>
    <?php foreach ($cashiers as $cashier): ?>
    <tr>
        <td><?= $cashier['cashier_id'] ?></td>
        <td><?= $cashier['username'] ?></td>
        <td><?= $cashier['email'] ?></td>
        <td>
            <?php if ($cashier['is_active']): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="cashier_id" value="<?= $cashier['cashier_id'] ?>">
                    <button type="submit" name="suspend_cashier">Suspend</button>
                </form>
            <?php else: ?>
                Suspended
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
</body>
</html>
