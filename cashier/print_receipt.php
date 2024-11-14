<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $cashier_id = $_SESSION['cashier_id'];  // Assuming cashier ID is stored in the session

    // Fetch booking and client details
    $stmt = $conn->prepare("SELECT bookings.*, clients.name AS client_name, clients.contact_number 
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
                    margin: 0;
                    padding: 0;
                }

                .receipt-container {
                    width: 100%;
                    margin: 0;
                    padding: 20px;
                    background-color: #ffffff;
                    border: 5px solid #0066cc; /* Blue border */
                    border-radius: 10px;
                    box-sizing: border-box;
                    page-break-after: always;
                }

                /* Ensure the receipt fills the entire A4 page */
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                    .receipt-container {
                        width: 100%;
                        max-width: 100%;
                        padding: 20px;
                        box-sizing: border-box;
                        page-break-after: always;
                    }
                    .receipt-footer button {
                        display: none; /* Hide the print button in print view */
                    }
                }

                .receipt-header {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .receipt-header h1 {
                    font-size: 32px;
                    color: #0066cc;
                    margin: 0;
                }

                .receipt-header h2 {
                    font-size: 18px;
                    color: #333;
                    margin: 5px 0;
                }

                .receipt-body {
                    margin: 15px 0;
                    font-size: 14px;
                }

                .receipt-body table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .receipt-body th, .receipt-body td {
                    padding: 8px;
                    border: 1px solid #ddd;
                    text-align: left;
                }

                .receipt-body th {
                    background-color: #0066cc;
                    color: white;
                }

                .receipt-footer {
                    text-align: center;
                    margin-top: 20px;
                }

                .receipt-footer p {
                    font-size: 14px;
                    color: #333;
                }

                .btn {
                    display: inline-block;
                    padding: 8px 12px;
                    border-radius: 5px;
                    font-size: 14px;
                    color: white;
                    text-align: center;
                    text-decoration: none;
                }

                .btn-add {
                    background-color: #28a745;
                }

                .btn-suspend {
                    background-color: #dc3545;
                }

            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="receipt-header">
                    <h1>Company Name</h1>
                    <h2>Receipt</h2>
                    <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['booking_id']) ?></p>
                </div>
                <div class="receipt-body">
                    <table>
                        <tr><th>Client Name</th><td><?= htmlspecialchars($booking['client_name']) ?></td></tr>
                        <tr><th>Contact Number</th><td><?= htmlspecialchars($booking['contact_number']) ?></td></tr>
                        <tr><th>Description</th><td><?= htmlspecialchars($booking['cargo_description']) ?></td></tr>
                        <tr><th>Weight (kg)</th><td><?= htmlspecialchars($booking['weight']) ?></td></tr>
                        <tr><th>Source</th><td><?= htmlspecialchars($booking['source']) ?></td></tr>
                        <tr><th>Destination</th><td><?= htmlspecialchars($booking['destination']) ?></td></tr>
                        <tr><th>Distance (km)</th><td><?= htmlspecialchars($booking['distance']) ?></td></tr>
                        <tr><th>Cost</th><td>Ksh.<?= htmlspecialchars($booking['cost']) ?></td></tr>
                        <tr><th>Payment Status</th><td><?= htmlspecialchars($booking['status']) ?></td></tr>
                        <tr><th>Cashier</th><td>You were served by: <?= htmlspecialchars($cashier_name) ?></td></tr>
                    </table>
                </div>
                <div class="receipt-footer">
                    <p><strong>Customer Signature:</strong> __________________________</p>
                    <p>Date: <?= date("Y-m-d") ?></p>
                    <br>
                    <p><strong>Thank you for choosing our services!</strong></p>
                </div>
            </div>
            <div class="receipt-footer" style="text-align:center; margin-top:30px;">
                <button onclick="window.print()" class="btn btn-add">Print Receipt</button>
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
?>
