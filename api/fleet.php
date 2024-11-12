<?php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM fleet";
    $result = $conn->query($sql);
    $fleets = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $fleets[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($fleets);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_name = $_POST['vehicle_name'];
    $status = $_POST['status'];

    $sql = "INSERT INTO fleet (vehicle_name, status) VALUES ('$vehicle_name', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "New fleet created successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $conn->error]);
    }
}
?>
