<?php
/**
 * Reset Admin Password
 * This script resets the admin password to: admin123
 */

require 'db/db.php';

echo "Resetting admin password...\n\n";

// New password: admin123
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$check = mysqli_query($conn, "SELECT id, email FROM users WHERE role='admin' LIMIT 1");

if(mysqli_num_rows($check) > 0) {
    $admin = mysqli_fetch_assoc($check);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $admin['id']);
    
    if($stmt->execute()) {
        echo "✅ Admin password reset successfully!\n\n";
        echo "Admin Login Credentials:\n";
        echo "========================\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Password: admin123\n";
        echo "========================\n\n";
        echo "You can now login at: http://localhost:8000/auth/admin_login.php\n";
    } else {
        echo "❌ Error updating password: " . $stmt->error . "\n";
    }
    $stmt->close();
} else {
    echo "❌ No admin user found in database!\n";
    echo "Creating new admin user...\n\n";
    
    // Create new admin
    $name = "System Admin";
    $email = "admin@test.com";
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if($stmt->execute()) {
        echo "✅ Admin user created successfully!\n\n";
        echo "Admin Login Credentials:\n";
        echo "========================\n";
        echo "Email: admin@test.com\n";
        echo "Password: admin123\n";
        echo "========================\n";
    } else {
        echo "❌ Error creating admin: " . $stmt->error . "\n";
    }
    $stmt->close();
}

mysqli_close($conn);
?>
