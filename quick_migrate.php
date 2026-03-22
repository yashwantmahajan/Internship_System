<?php
/**
 * Quick Database Migration - Add Profile Fields
 * This adds all necessary profile fields without dependencies
 */

require 'db/db.php';

echo "Adding profile fields to database...\n\n";

// Helper function to add column safely
function addColumn($conn, $table, $column, $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if(mysqli_num_rows($check) == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if(mysqli_query($conn, $sql)) {
            echo "✅ Added $table.$column\n";
            return true;
        } else {
            echo "❌ Error adding $table.$column: " . mysqli_error($conn) . "\n";
            return false;
        }
    } else {
        echo "ℹ️  $table.$column already exists\n";
        return true;
    }
}

// STUDENT FIELDS
echo "=== STUDENTS TABLE ===\n";
addColumn($conn, 'students', 'preferred_location', "VARCHAR(100) DEFAULT '' AFTER degree");
addColumn($conn, 'students', 'city', "VARCHAR(100) DEFAULT '' AFTER preferred_location");
addColumn($conn, 'students', 'year_of_study', "VARCHAR(50) DEFAULT '' AFTER degree");
addColumn($conn, 'students', 'percentage', "DECIMAL(5,2) DEFAULT 0.00 AFTER year_of_study");
addColumn($conn, 'students', 'skills', "TEXT AFTER percentage");
addColumn($conn, 'students', 'bio', "TEXT AFTER skills");
addColumn($conn, 'students', 'linkedin_url', "VARCHAR(255) DEFAULT '' AFTER bio");
addColumn($conn, 'students', 'github_url', "VARCHAR(255) DEFAULT '' AFTER linkedin_url");
addColumn($conn, 'students', 'portfolio_url', "VARCHAR(255) DEFAULT '' AFTER github_url");

echo "\n=== COMPANIES TABLE ===\n";
addColumn($conn, 'companies', 'city', "VARCHAR(100) DEFAULT '' AFTER address");
addColumn($conn, 'companies', 'website', "VARCHAR(255) DEFAULT '' AFTER industry");
addColumn($conn, 'companies', 'founded_year', "INT DEFAULT NULL AFTER website");
addColumn($conn, 'companies', 'team_size', "VARCHAR(50) DEFAULT '' AFTER founded_year");
addColumn($conn, 'companies', 'description', "TEXT AFTER team_size");
addColumn($conn, 'companies', 'linkedin_url', "VARCHAR(255) DEFAULT '' AFTER description");
addColumn($conn, 'companies', 'twitter_url', "VARCHAR(255) DEFAULT '' AFTER linkedin_url");

echo "\n✅ Migration completed!\n";
echo "You can now save your profile without errors.\n";

mysqli_close($conn);
?>
