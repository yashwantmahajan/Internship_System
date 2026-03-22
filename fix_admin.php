<?php
require 'db/db.php';

$email = 'admin@test.com';
$password = '123456';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Resetting Admin Password...\n";

// Check if admin exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update existing
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hash, $email);
    if ($stmt->execute()) {
        echo "SUCCESS: Admin password updated to '123456'.\n";
    } else {
        echo "ERROR: Failed to update password. " . $conn->error . "\n";
    }
} else {
    // Insert new
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', ?, ?, 'admin')");
    $stmt->bind_param("ss", $email, $hash);
    if ($stmt->execute()) {
        echo "SUCCESS: Admin account created with password '123456'.\n";
    } else {
        echo "ERROR: Failed to create admin. " . $conn->error . "\n";
    }
}
?>
