<?php
require 'db.php';  // Make sure the database connection is correct

// Fetch all users with their passwords (assuming you're using a table called 'admins')
$stmt = $conn->query("SELECT cashier_id, password FROM cashiers");
$admins = $stmt->fetchAll();

// Loop through each admin and hash their password
foreach ($cashiers as $cashier) {
    // Hash the password
    $hashedPassword = password_hash($cashier['password'], PASSWORD_DEFAULT);

    // Update the password in the database with the hashed one
    $updateStmt = $conn->prepare("UPDATE cashiers SET password = :password WHERE cashier_id = :cashier_id");
    $updateStmt->execute(['password' => $hashedPassword, 'cashier_id' => $admin['cashier_id']]);
}

echo "Passwords updated successfully.";
?>
