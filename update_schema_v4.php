<?php
require 'db/db.php';
echo "Updating Database Schema v4 (Resume Support)...\n";

$queries = [
    "ALTER TABLE students ADD COLUMN resume_path VARCHAR(255) NULL AFTER degree;"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "✅ Success: $query\n";
    } else {
        echo "❌ Error: " . $conn->error . "\n";
    }
}
echo "Schema Update V4 Complete.\n";
?>
