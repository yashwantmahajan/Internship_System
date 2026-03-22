<?php
$host = "localhost";
$user = "root";
$pass = "";

echo "Checking MySQL connection...\n";
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "Connected as root.\n";

$result = $conn->query("SHOW DATABASES LIKE 'internship_system'");
if ($result->num_rows > 0) {
    echo "Database 'internship_system' EXISTS.\n";
} else {
    echo "Database 'internship_system' DOES NOT EXIST.\n";
}
?>
