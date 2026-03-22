<?php
require 'db/db.php';

echo "Seeding Database...\n";

// 1. Create Sample Company User
$comp_name = "TechSolutions Inc.";
$comp_email = "hr@techsolutions.com";
$comp_pass = password_hash("company123", PASSWORD_DEFAULT);

// Check if user exists
$check = $conn->query("SELECT id FROM users WHERE email='$comp_email'");
if($check->num_rows == 0){
    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$comp_name', '$comp_email', '$comp_pass', 'company')");
    $comp_user_id = $conn->insert_id;
    echo "Created Company User: $comp_email / company123\n";
    
    // Create Company Profile
    $conn->query("INSERT INTO companies (user_id, company_name, full_name, email, industry) VALUES ($comp_user_id, '$comp_name', 'HR Manager', '$comp_email', 'Software Development')");
    $company_id = $conn->insert_id;
} else {
    $row = $check->fetch_assoc();
    $comp_user_id = $row['id'];
    $c_row = $conn->query("SELECT id FROM companies WHERE user_id=$comp_user_id")->fetch_assoc();
    $company_id = $c_row['id'];
    echo "Company User already exists.\n";
}

// 2. Post Sample Internships
$internships = [
    [
        "title" => "Frontend Developer Intern",
        "description" => "We are looking for a passionate Frontend Developer Intern to join our team. You will work with React.js and Tailwind CSS.",
        "location" => "Remote",
        "type" => "Paid",
        "stipend" => 15000
    ],
    [
        "title" => "Data Science Intern",
        "description" => "Join our data team to analyze large datasets and build predictive models using Python and SQL.",
        "location" => "Bangalore",
        "type" => "Paid",
        "stipend" => 20000
    ],
    [
        "title" => "UI/UX Designer",
        "description" => "Design intuitive user interfaces for our web and mobile applications.",
        "location" => "Mumbai",
        "type" => "Unpaid",
        "stipend" => 0
    ]
];

foreach($internships as $internship){
    $title = $internship['title'];
    $check_int = $conn->query("SELECT id FROM internships WHERE title='$title' AND company_id=$company_id");
    
    if($check_int->num_rows == 0){
        $desc = $conn->real_escape_string($internship['description']);
        $loc = $internship['location'];
        $type = $internship['type'];
        $stipend = $internship['stipend'];
        
        $conn->query("INSERT INTO internships (company_id, title, description, status, location, payment_type, stipend) 
                      VALUES ($company_id, '$title', '$desc', 'active', '$loc', '$type', $stipend)");
        echo "Posted Internship: $title\n";
    }
}

// 3. Create Sample Student (if not exists)
$stud_name = "Rahul Sharma";
$stud_email = "rahul@student.com";
$stud_pass = password_hash("student123", PASSWORD_DEFAULT);

$check_s = $conn->query("SELECT id FROM users WHERE email='$stud_email'");
if($check_s->num_rows == 0){
    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$stud_name', '$stud_email', '$stud_pass', 'student')");
    $stud_user_id = $conn->insert_id;
    echo "Created Student User: $stud_email / student123\n";

    // Create Student Profile
    $conn->query("INSERT INTO students (user_id, phone, college, degree) VALUES ($stud_user_id, '9876543210', 'IIT Bombay', 'B.Tech Computer Science')");
    $student_id = $conn->insert_id;
} else {
    $row = $check_s->fetch_assoc();
    $stud_user_id = $row['id'];
    $s_row = $conn->query("SELECT id FROM students WHERE user_id=$stud_user_id")->fetch_assoc();
    $student_id = $s_row['id'];
    echo "Student User already exists.\n";
}

// 4. Create Sample Application
// Apply Rahul to "Frontend Developer Intern"
$int_row = $conn->query("SELECT id FROM internships WHERE title='Frontend Developer Intern' LIMIT 1")->fetch_assoc();
if($int_row && $student_id){
    $int_id = $int_row['id'];
    $check_app = $conn->query("SELECT id FROM applications WHERE internship_id=$int_id AND student_id=$student_id");
    
    if($check_app->num_rows == 0){
        $conn->query("INSERT INTO applications (internship_id, student_id, status) VALUES ($int_id, $student_id, 'approved')");
        echo "Created Sample Application: Rahul applied to Frontend Developer Intern\n";
    }
}

echo "Database Seeding Complete.\n";
?>
