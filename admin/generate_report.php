<?php
require 'db.php'; // Include database connection

// Get driver ID from URL
$driver_id = $_GET['driver_id'];

// Fetch all trips made by the driver
$stmt = $conn->prepare("SELECT * FROM trips WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch driver details
$stmt = $conn->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

// Generate report (for example, simple HTML table)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Report: <?= htmlspecialchars($driver['name']) ?></title>
</head>
<body>
    <h1>Report for Driver: <?= htmlspecialchars($driver['name']) ?></h1>
    <h3>Email: <?= htmlspecialchars($driver['email']) ?> | Phone: <?= htmlspecialchars($driver['phone']) ?></h3>
    
    <h2>Trips Report</h2>
    <table border="1">
        <tr><th>Trip Date</th><th>Customer Name</th><th>Start Location</th><th>End Location</th></tr>
        <?php foreach ($trips as $trip): ?>
        <tr>
            <td><?= htmlspecialchars($trip['trip_date']) ?></td>
            <td><?= htmlspecialchars($trip['customer_name']) ?></td>
            <td><?= htmlspecialchars($trip['start_location']) ?></td>
            <td><?= htmlspecialchars($trip['end_location']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
