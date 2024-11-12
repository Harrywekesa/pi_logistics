<?php
session_start();
require 'db.php'; // Ensure this file contains the correct database connection setup

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password is correct
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <main class="login-container">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
