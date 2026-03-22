<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "internship_system";

// 1. Try connecting to MySQL server only (no DB)
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if database exists
try {
    $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db'");
    
    if ($db_check->num_rows == 0) {
        // Database doesn't exist, create it
        if ($conn->query("CREATE DATABASE $db") === TRUE) {
            $conn->select_db($db);
            
            // Import schema if possible
            $sqlFile = __DIR__ . '/../database.sql';
            if (file_exists($sqlFile)) {
                $queries = file_get_contents($sqlFile);
                if ($conn->multi_query($queries)) {
                    while ($conn->next_result()) {;} // flush results
                }
            }
        } else {
            die("Error creating database: " . $conn->error);
        }
    } else {
        $conn->select_db($db);
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
