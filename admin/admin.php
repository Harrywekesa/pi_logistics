<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Link to external CSS file -->
</head>
<body>

    <!-- Video Background -->
    <div class="video-wrapper">
        <video class="video-background" autoplay muted loop>
            <source src="../videos/Bomba1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

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
    
    <div class="content">
        <!-- Admin Dashboard Content -->
        <h1>Welcome to the Admin Dashboard</h1>
        <p>Welcome to Pi Logistics – Your trusted partner for efficient transportation and fleet management!</p>
        <!-- Add your admin-specific content here -->
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Pi Logistics. All rights reserved.</p>
    </footer>

</body>
</html>
