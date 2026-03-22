<?php
require 'db/db.php';

// Add resume_path column to students table if it doesn't exist
$sql = "SHOW COLUMNS FROM students LIKE 'resume_path'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alterSql = "ALTER TABLE students ADD COLUMN resume_path VARCHAR(255) DEFAULT NULL";
    if ($conn->query($alterSql) === TRUE) {
        echo "Column 'resume_path' added successfully to 'students' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'resume_path' already exists.\n";
}

// Check if uploads/resumes directory exists
$uploadDir = __DIR__ . '/uploads/resumes';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "Directory 'uploads/resumes' created successfully.\n";
    } else {
        echo "Error creating directory 'uploads/resumes'.\n";
    }
} else {
    echo "Directory 'uploads/resumes' already exists.\n";
}

echo "Schema update complete.";
?>
