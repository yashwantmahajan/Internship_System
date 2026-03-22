<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Attempting connection to localhost:3306 with user 'root'...\n";

try {
    $conn = new mysqli("localhost", "root", "Yashwant@1408", "", 3306);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    echo "Connected successfully to MySQL server!\n";
    
    // Check if db exists
    $db_selected = $conn->select_db("internship_system");
    if (!$db_selected) {
        echo "Database 'internship_system' DOES NOT exist. Trying to create it...\n";
        if ($conn->query("CREATE DATABASE internship_system")) {
            echo "Database created successfully.\n";
            // Now we can import... but let's just create it for now.
        } else {
            echo "Error creating database: " . $conn->error . "\n";
        }
    } else {
        echo "Database 'internship_system' exists.\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>
