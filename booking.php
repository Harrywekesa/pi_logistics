<!-- booking.php -->
<?php
session_start();
require 'db.php'; // Include database connection

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $pickup = $_POST['pickup'];
    $destination = $_POST['destination'];
    $date_of_transport = $_POST['date'];

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, name, email, phone, pickup, destination, date_of_transport) VALUES (:user_id, :name, :email, :phone, :pickup, :destination, :date_of_transport)");
    $stmt->execute([
        'user_id' => $user_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'pickup' => $pickup,
        'destination' => $destination,
        'date_of_transport' => $date_of_transport
    ]);

    $success_message = "Thank you, $name! Your booking from $pickup to $destination on $date_of_transport has been received.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Transportation</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <main class="booking-container">
        <h2>Book Your Transportation</h2>
        <a href="logout.php">Logout</a>

        <?php if (!empty($success_message)) : ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php else : ?>
            <form action="booking.php" method="post">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" id="phone" required>

                <label for="pickup">Pickup Location:</label>
                <input type="text" name="pickup" id="pickup" required>

                <label for="destination">Destination:</label>
                <input type="text" name="destination" id="destination" required>

                <label for="date">Date of Transport:</label>
                <input type="date" name="date" id="date" required>

                <button type="submit">Book Now</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
