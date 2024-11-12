<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Pi Logistics</title>
    <link rel="stylesheet" href="styles/style.css"> <!-- Link to an external CSS file -->
</head>
<body>
    <!-- Navigation Bar -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">Pi Logistics</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="admin/admin.php">Admin</a></li>
                <li><a href="login.php" class="login-btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="welcome-section">
            <h1>Pi Logistics</h1>
            <p>We provide reliable and efficient transportation services tailored to meet your needs. Whether you need a one-time ride or regular transport, our team is here to help you reach your destination safely and comfortably.</p>
            <button onclick="window.location.href='booking.php'" class="book-btn">Book Transportation</button>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Pi Logistics. All rights reserved.</p>
    </footer>
</body>
</html>
