<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Pi Logistics</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Link to external CSS file -->
</head>
<body>
    <!-- Navigation Bar -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">Pi Logistics</a>
            </div>
            <ul class="nav-links">
                <li><a href="admin.php">Admin Dashboard</a></li>
                <li><a href="login.php" class="login-btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="welcome-section">
            <h1>Welcome to Pi Logistics</h1>
            <p>Welcome to the Pi Logistics administrator’s page. We provide efficient, safe, and reliable transportation solutions tailored to meet client needs. Whether for one-time rides or regular transport, Pi Logistics ensures your journey is comfortable and secure.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date("Y"); ?> Pi Logistics. All rights reserved.</p>
    </footer>
</body>
</html>
