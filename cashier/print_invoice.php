<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $cashier_id = $_SESSION['cashier_id'];

    // Fetch booking and client details
    $stmt = $conn->prepare("SELECT bookings.*, clients.name AS client_name, clients.contact_number, 
                            clients.address 
                            FROM bookings 
                            JOIN clients ON bookings.client_id = clients.client_id 
                            WHERE booking_id = :booking_id");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Fetch cashier's name using cashier_id from session
        $stmt_cashier = $conn->prepare("SELECT name FROM cashiers WHERE cashier_id = :cashier_id");
        $stmt_cashier->bindParam(':cashier_id', $cashier_id, PDO::PARAM_INT);
        $stmt_cashier->execute();
        $cashier = $stmt_cashier->fetch(PDO::FETCH_ASSOC);
        $cashier_name = $cashier ? $cashier['name'] : 'Unknown Cashier';  // Default if cashier not found

        ob_start();
        ?>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                }
                .invoice-container {
                    width: 100%;
                    max-width: 700px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border: 5px solid #0066cc;
                    border-radius: 10px;
                    box-sizing: border-box;
                }
                /* Print styles */
                @media print {
                    .print-button {
                        display: none;
                    }
                }
                .invoice-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .invoice-header h1 {
                    font-size: 32px;
                    color: #0066cc;
                }
                .invoice-body {
                    font-size: 14px;
                }
                .invoice-body table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .invoice-body th, .invoice-body td {
                    padding: 8px;
                    border: 1px solid #ddd;
                    text-align: left;
                }
                .invoice-body th {
                    background-color: #0066cc;
                    color: white;
                }
                .invoice-footer {
                    text-align: center;
                    margin-top: 20px;
                }
                .print-button {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    color: white;
                    background-color: #28a745;
                    border-radius: 5px;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="invoice-container">
                <div class="invoice-header">
                    <h1>BAMBOO LOGISTICS</h1>
                    <h2>Invoice</h2>
                    <p><strong>Invoice No:</strong> INV-<?= htmlspecialchars($booking['booking_id']) ?></p>
                    <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>
                </div>
                <div class="invoice-body">
                    <table>
                        <tr><th>Client Name</th><td><?= htmlspecialchars($booking['client_name']) ?></td></tr>
                        <tr><th>Contact Number</th><td><?= htmlspecialchars($booking['contact_number']) ?></td></tr>
                        <tr><th>Address</th><td><?= htmlspecialchars($booking['address']) ?></td></tr>
                        <tr><th>Description</th><td><?= htmlspecialchars($booking['cargo_description']) ?></td></tr>
                        <tr><th>Weight (kg)</th><td><?= htmlspecialchars($booking['weight']) ?></td></tr>
                        <tr><th>Source</th><td><?= htmlspecialchars($booking['source']) ?></td></tr>
                        <tr><th>Destination</th><td><?= htmlspecialchars($booking['destination']) ?></td></tr>
                        <tr><th>Distance (km)</th><td><?= htmlspecialchars($booking['distance']) ?></td></tr>
                        <tr><th>Total Cost</th><td>Ksh.<?= htmlspecialchars($booking['cost']) ?></td></tr>
                        <tr><th>Payment Status</th><td><?= htmlspecialchars($booking['']) ?></td></tr>
                        <tr><th>Delivery Status</th><td><?= htmlspecialchars($booking['status']) ?></td></tr>
                        <tr><th>Cashier</th><td>Served by: <?= htmlspecialchars($cashier_name) ?></td></tr>
                    </table>
                </div>
                <div class="invoice-footer">
                    <p>Payment Terms: Please make payment within 15 days.</p>
                    <p><strong>Thank you for your business!</strong></p>
                </div>
            </div>
            <div class="invoice-footer" style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()" class="print-button">Print Invoice</button>
            </div>
        </body>
        </html>
        <?php
        ob_end_flush();
    } else {
        echo "Error: Booking not found.";
    }
} else {
    echo "Error: Invalid request.";
}
