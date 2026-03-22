<?php
$host = "localhost";
$user = "root";
$pass = ""; // Try empty password
$db   = "internship_system";

try {
    $conn = mysqli_connect($host, $user, $pass, $db, 3306);
    if ($conn) {
        echo "SUCCESS: Connected with empty password.";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage();
}
?>
