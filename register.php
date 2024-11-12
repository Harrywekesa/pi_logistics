<!-- register.php -->
<?php
session_start();
require 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Username or email already taken.";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
        $stmt->execute(['username' => $username, 'password' => $password, 'email' => $email]);

        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location: booking.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <main class="register-container">
        <h2>Register</h2>
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </main>
</body>
</html>
