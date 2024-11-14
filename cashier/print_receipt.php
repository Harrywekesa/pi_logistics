<?php
session_start();
require '../db.php';
require '../vendor/autoload.php'; // Adjust the path to autoload.php

use spipu\html2Pdf\html2Pdf; // Use the correct namespace

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $cashier_name = $_SESSION['cashier_name'];

    // Fetch booking and client details
    $stmt = $conn->prepare("SELECT bookings.*, clients.name AS client_name, clients.contact_number 
                            FROM bookings 
                            JOIN clients ON bookings.client_id = clients.client_id 
                            WHERE booking_id = :booking_id");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        ob_start();
        ?>
        <html>
        <head>
            <style>
                /* Insert CSS directly to style the PDF */
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                }
                .receipt-container {
                    padding: 20px;
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    width: 90%;
                    margin: auto;
                    font-size: 14px;
                }
                .receipt-header, .receipt-footer {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .receipt-header h2 {
                    color: #0066cc;
                    font-size: 24px;
                }
                .receipt-body {
                    margin: 15px 0;
                }
                .receipt-body table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 10px 0;
                }
                .receipt-body table, .receipt-body th, .receipt-body td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                .receipt-body th {
                    background-color: #333;
                    color: white;
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
                    <h2>Receipt</h2>
                    <p>Booking ID: <?= htmlspecialchars($booking['booking_id']) ?></p>
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
                        <tr><th>Cost</th><td><?= htmlspecialchars($booking['cost']) ?> USD</td></tr>
                        <tr><th>Payment Status</th><td><?= htmlspecialchars($booking['status']) ?></td></tr>
                        <tr><th>Cashier</th><td><?= htmlspecialchars($cashier_name) ?></td></tr>
                    </table>
                </div>
                <div class="receipt-footer">
                    <p><strong>Customer Signature:</strong> __________________________</p>
                    <p>Date: <?= date("Y-m-d") ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        // Generate PDF
        $pdf = new html2Pdf(); // Instantiate Html2Pdf
        $pdf->writeHTML($html);
        $pdf->output('receipt.pdf');
    } else {
        echo "Error: Booking not found.";
    }
} else {
    echo "Error: Invalid request.";
}
