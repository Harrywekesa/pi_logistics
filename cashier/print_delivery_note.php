<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $cashier_name = $_SESSION['cashier_name'];

    // Fetch booking and client details
    $stmt = $conn->prepare("SELECT bookings.*, clients.name AS client_name, clients.contact_number, clients.address 
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
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                }
                .delivery-note-container {
                    padding: 20px;
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    width: 90%;
                    margin: auto;
                }
                .delivery-note-header, .delivery-note-footer {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .delivery-note-header h2 {
                    color: #0066cc;
                    font-size: 24px;
                }
                .delivery-note-body {
                    margin: 15px 0;
                }
                .delivery-note-body table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 10px 0;
                }
                .delivery-note-body table, .delivery-note-body th, .delivery-note-body td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                .delivery-note-body th {
                    background-color: #333;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="delivery-note-container">
                <div class="delivery-note-header">
                    <h2>Delivery Note</h2>
                    <p>Booking ID: <?= htmlspecialchars($booking['booking_id']) ?></p>
                </div>
                <div class="delivery-note-body">
                    <table>
                        <tr><th>Client Name</th><td><?= htmlspecialchars($booking['client_name']) ?></td></tr>
                        <tr><th>Contact Number</th><td><?= htmlspecialchars($booking['contact_number']) ?></td></tr>
                        <tr><th>Pickup Location</th><td><?= htmlspecialchars($booking['source']) ?></td></tr>
                        <tr><th>Destination</th><td><?= htmlspecialchars($booking['destination']) ?></td></tr>
                        <tr><th>Expected Delivery Date</th><td><?= htmlspecialchars($booking['date_of_transport']) ?></td></tr>
                        <tr><th>Description</th><td><?= htmlspecialchars($booking['cargo_description']) ?></td></tr>
                        <tr><th>Weight (kg)</th><td><?= htmlspecialchars($booking['weight']) ?></td></tr>
                        <tr><th>Distance (km)</th><td><?= htmlspecialchars($booking['distance']) ?></td></tr>
                    </table>
                </div>
                <div class="delivery-note-footer">
                    <p>Customer Confirmation Signature:</p>
                    <p>____________________________</p>
                    <p>Date: <?= date("Y-m-d") ?></p>
                    <p>Served by: <?= htmlspecialchars($cashier_name) ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        // Generate PDF
        $pdf = new Html2Pdf();
        $pdf->writeHTML($html);
        $pdf->output('delivery_note.pdf');
    } else {
        echo "Error: Booking not found.";
    }
} else {
    echo "Error: Invalid request.";
}
