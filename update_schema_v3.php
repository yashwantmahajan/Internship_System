<?php
/**
 * Database Schema Update v3
 * Adds comprehensive profile fields for students and companies
 */

require 'db/db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Database Schema Update v3 - Profile Enhancement</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #10b981; padding: 10px; background: #d1fae5; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; padding: 10px; background: #fee2e2; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; padding: 10px; background: #dbeafe; border-radius: 5px; margin: 10px 0; }
        h1 { color: #1f2937; }
        .query { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 0.9em; }
    </style>
</head>
<body>
<h1>🔄 Database Schema Update v3 - Profile Enhancement</h1>
<p>Adding comprehensive profile fields for students and companies...</p>
";

$errors = [];

// ===== STUDENT PROFILE ENHANCEMENTS =====
echo "<h2>1. Enhancing Students Table</h2>";

$student_columns = [
    "city VARCHAR(100) DEFAULT '' AFTER preferred_location" => "city",
    "year_of_study VARCHAR(50) DEFAULT '' AFTER degree" => "year_of_study",
    "percentage DECIMAL(5,2) DEFAULT 0.00 AFTER year_of_study" => "percentage",
    "skills TEXT AFTER percentage" => "skills",
    "bio TEXT AFTER skills" => "bio",
    "linkedin_url VARCHAR(255) DEFAULT '' AFTER bio" => "linkedin_url",
    "github_url VARCHAR(255) DEFAULT '' AFTER linkedin_url" => "github_url",
    "portfolio_url VARCHAR(255) DEFAULT '' AFTER github_url" => "portfolio_url"
];

foreach($student_columns as $column_def => $column_name) {
    $query = "ALTER TABLE students ADD COLUMN $column_def";
    if(mysqli_query($conn, $query)) {
        echo "<div class='success'>✅ Added '$column_name' column to students table</div>";
    } else {
        $error = mysqli_error($conn);
        if(strpos($error, 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Column '$column_name' already exists</div>";
        } else {
            echo "<div class='error'>❌ Error adding '$column_name': $error</div>";
            $errors[] = "Students.$column_name: $error";
        }
    }
}

// ===== STUDENT EDUCATION TABLE =====
echo "<h2>2. Creating Student Education Table</h2>";
$education_table = "
CREATE TABLE IF NOT EXISTS student_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    degree_type VARCHAR(100) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    field_of_study VARCHAR(255) NOT NULL,
    start_year INT NOT NULL,
    end_year INT,
    percentage DECIMAL(5,2),
    currently_studying BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if(mysqli_query($conn, $education_table)) {
    echo "<div class='success'>✅ Created student_education table</div>";
    echo "<div class='query'>$education_table</div>";
} else {
    $error = mysqli_error($conn);
    if(strpos($error, 'already exists') !== false) {
        echo "<div class='info'>ℹ️ Table student_education already exists</div>";
    } else {
        echo "<div class='error'>❌ Error: $error</div>";
        $errors[] = "student_education table: $error";
    }
}

// ===== STUDENT EXPERIENCE TABLE =====
echo "<h2>3. Creating Student Experience Table</h2>";
$experience_table = "
CREATE TABLE IF NOT EXISTS student_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    location VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    currently_working BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if(mysqli_query($conn, $experience_table)) {
    echo "<div class='success'>✅ Created student_experience table</div>";
    echo "<div class='query'>$experience_table</div>";
} else {
    $error = mysqli_error($conn);
    if(strpos($error, 'already exists') !== false) {
        echo "<div class='info'>ℹ️ Table student_experience already exists</div>";
    } else {
        echo "<div class='error'>❌ Error: $error</div>";
        $errors[] = "student_experience table: $error";
    }
}

// ===== STUDENT CERTIFICATES TABLE =====
echo "<h2>4. Creating Student Certificates Table</h2>";
$certificates_table = "
CREATE TABLE IF NOT EXISTS student_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    certificate_name VARCHAR(255) NOT NULL,
    issuing_organization VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    credential_id VARCHAR(255),
    credential_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if(mysqli_query($conn, $certificates_table)) {
    echo "<div class='success'>✅ Created student_certificates table</div>";
    echo "<div class='query'>$certificates_table</div>";
} else {
    $error = mysqli_error($conn);
    if(strpos($error, 'already exists') !== false) {
        echo "<div class='info'>ℹ️ Table student_certificates already exists</div>";
    } else {
        echo "<div class='error'>❌ Error: $error</div>";
        $errors[] = "student_certificates table: $error";
    }
}

// ===== COMPANY PROFILE ENHANCEMENTS =====
echo "<h2>5. Enhancing Companies Table</h2>";

$company_columns = [
    "website VARCHAR(255) DEFAULT '' AFTER industry" => "website",
    "founded_year INT DEFAULT NULL AFTER website" => "founded_year",
    "team_size VARCHAR(50) DEFAULT '' AFTER founded_year" => "team_size",
    "description TEXT AFTER team_size" => "description",
    "linkedin_url VARCHAR(255) DEFAULT '' AFTER description" => "linkedin_url",
    "twitter_url VARCHAR(255) DEFAULT '' AFTER linkedin_url" => "twitter_url"
];

foreach($company_columns as $column_def => $column_name) {
    $query = "ALTER TABLE companies ADD COLUMN $column_def";
    if(mysqli_query($conn, $query)) {
        echo "<div class='success'>✅ Added '$column_name' column to companies table</div>";
    } else {
        $error = mysqli_error($conn);
        if(strpos($error, 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Column '$column_name' already exists</div>";
        } else {
            echo "<div class='error'>❌ Error adding '$column_name': $error</div>";
            $errors[] = "Companies.$column_name: $error";
        }
    }
}

// Summary
echo "<h2>📊 Summary</h2>";
if(empty($errors)) {
    echo "<div class='success'><strong>✅ Profile enhancement migration completed successfully!</strong></div>";
    echo "<p><strong>Student Profile Enhancements:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Added: city, year_of_study, percentage, skills, bio</li>";
    echo "<li>✅ Added: linkedin_url, github_url, portfolio_url</li>";
    echo "<li>✅ Created: student_education table</li>";
    echo "<li>✅ Created: student_experience table</li>";
    echo "<li>✅ Created: student_certificates table</li>";
    echo "</ul>";
    echo "<p><strong>Company Profile Enhancements:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Added: website, founded_year, team_size</li>";
    echo "<li>✅ Added: description, linkedin_url, twitter_url</li>";
    echo "</ul>";
} else {
    echo "<div class='error'><strong>⚠️ Migration completed with errors:</strong></div>";
    echo "<ul>";
    foreach($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>← Back to Home</a></p>";

echo "</body></html>";

mysqli_close($conn);
?>
