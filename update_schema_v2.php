<?php
/**
 * Database Schema Update v2
 * Adds location-based features to the internship system
 */

require 'db/db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Database Schema Update v2</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #10b981; padding: 10px; background: #d1fae5; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; padding: 10px; background: #fee2e2; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; padding: 10px; background: #dbeafe; border-radius: 5px; margin: 10px 0; }
        h1 { color: #1f2937; }
        .query { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
    </style>
</head>
<body>
<h1>🔄 Database Schema Update v2</h1>
<p>Adding location-based features to the internship system...</p>
";

$updates = [];
$errors = [];

// 1. Add city column to companies table
echo "<h2>1. Updating Companies Table</h2>";
$query1 = "ALTER TABLE companies ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT '' AFTER address";
if(mysqli_query($conn, $query1)) {
    echo "<div class='success'>✅ Added 'city' column to companies table</div>";
    echo "<div class='query'>$query1</div>";
} else {
    // Try without IF NOT EXISTS for MySQL versions that don't support it
    $query1_alt = "ALTER TABLE companies ADD COLUMN city VARCHAR(100) DEFAULT '' AFTER address";
    if(mysqli_query($conn, $query1_alt)) {
        echo "<div class='success'>✅ Added 'city' column to companies table</div>";
        echo "<div class='query'>$query1_alt</div>";
    } else {
        $error = mysqli_error($conn);
        if(strpos($error, 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Column 'city' already exists in companies table</div>";
        } else {
            echo "<div class='error'>❌ Error: $error</div>";
            $errors[] = "Companies table: $error";
        }
    }
}

// 2. Add preferred_location column to students table
echo "<h2>2. Updating Students Table</h2>";
$query2 = "ALTER TABLE students ADD COLUMN IF NOT EXISTS preferred_location VARCHAR(100) DEFAULT '' AFTER degree";
if(mysqli_query($conn, $query2)) {
    echo "<div class='success'>✅ Added 'preferred_location' column to students table</div>";
    echo "<div class='query'>$query2</div>";
} else {
    $query2_alt = "ALTER TABLE students ADD COLUMN preferred_location VARCHAR(100) DEFAULT '' AFTER degree";
    if(mysqli_query($conn, $query2_alt)) {
        echo "<div class='success'>✅ Added 'preferred_location' column to students table</div>";
        echo "<div class='query'>$query2_alt</div>";
    } else {
        $error = mysqli_error($conn);
        if(strpos($error, 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Column 'preferred_location' already exists in students table</div>";
        } else {
            echo "<div class='error'>❌ Error: $error</div>";
            $errors[] = "Students table: $error";
        }
    }
}

// 3. Ensure internships.location is VARCHAR(255) - it should already exist
echo "<h2>3. Verifying Internships Table</h2>";
$query3 = "SHOW COLUMNS FROM internships LIKE 'location'";
$result = mysqli_query($conn, $query3);
if($result && mysqli_num_rows($result) > 0) {
    $column = mysqli_fetch_assoc($result);
    echo "<div class='success'>✅ Location column exists in internships table</div>";
    echo "<div class='info'>Type: {$column['Type']}, Null: {$column['Null']}, Default: {$column['Default']}</div>";
} else {
    echo "<div class='error'>❌ Location column not found in internships table</div>";
    $errors[] = "Internships table: location column missing";
}

// 4. Add indexes for better performance
echo "<h2>4. Adding Indexes for Performance</h2>";

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_companies_city ON companies(city)" => "companies.city",
    "CREATE INDEX IF NOT EXISTS idx_students_preferred_location ON students(preferred_location)" => "students.preferred_location",
    "CREATE INDEX IF NOT EXISTS idx_internships_location ON internships(location)" => "internships.location",
    "CREATE INDEX IF NOT EXISTS idx_internships_status ON internships(status)" => "internships.status"
];

foreach($indexes as $query => $description) {
    // Try with IF NOT EXISTS first
    if(mysqli_query($conn, $query)) {
        echo "<div class='success'>✅ Created index on $description</div>";
    } else {
        // Try without IF NOT EXISTS
        $query_alt = str_replace('IF NOT EXISTS ', '', $query);
        if(mysqli_query($conn, $query_alt)) {
            echo "<div class='success'>✅ Created index on $description</div>";
        } else {
            $error = mysqli_error($conn);
            if(strpos($error, 'Duplicate key') !== false || strpos($error, 'already exists') !== false) {
                echo "<div class='info'>ℹ️ Index on $description already exists</div>";
            } else {
                echo "<div class='error'>❌ Error creating index on $description: $error</div>";
            }
        }
    }
}

// 5. Update existing data with sample locations (optional)
echo "<h2>5. Sample Data Update (Optional)</h2>";
echo "<div class='info'>ℹ️ You can manually update existing records with location data through the profile pages.</div>";

// Summary
echo "<h2>📊 Summary</h2>";
if(empty($errors)) {
    echo "<div class='success'><strong>✅ Schema update completed successfully!</strong></div>";
    echo "<p>The database is now ready for location-based features.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Companies can now set their city in their profile</li>";
    echo "<li>Students can set their preferred location in their profile</li>";
    echo "<li>Internship postings will include location information</li>";
    echo "<li>Students can filter internships by location</li>";
    echo "</ul>";
} else {
    echo "<div class='error'><strong>⚠️ Schema update completed with errors:</strong></div>";
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
