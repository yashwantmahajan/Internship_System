<?php
// setup_database.php

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password

echo "1. Connecting to MySQL server...\n";
// Connect WITHOUT specifying database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "   - Connected successfully.\n\n";

// Create Database
echo "2. Creating database 'internship_system'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS internship_system";
if ($conn->query($sql) === TRUE) {
    echo "   - Database created (or already exists).\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// Select Database
$conn->select_db("internship_system");

// Import SQL file
echo "\n3. Importing tables from 'database.sql'...\n";
$sqlFile = 'database.sql';
if (!file_exists($sqlFile)) {
    die("Error: database.sql not found.\n");
}

$queries = file_get_contents($sqlFile);
// Remove comments and split by semicolon
// This is a basic splitter, usually good enough for dumps
$queries = explode(';', $queries);

$count = 0;
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if ($conn->query($query) === TRUE) {
            $count++;
        } else {
            echo "   [WARNING] Error executing query: " . substr($query, 0, 50) . "...\n";
            echo "   Error: " . $conn->error . "\n";
        }
    }
}

echo "   - Imported $count queries successfully.\n";

echo "\n============================================\n";
echo " SETUP COMPLETED SUCCESSFULLY \n";
echo "============================================\n";

$conn->close();
?>
