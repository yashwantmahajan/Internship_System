<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db/db.php';

$message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_raw = $_POST['password'];
    $phone = trim($_POST['phone']);
    $college = trim($_POST['college']);
    $degree = trim($_POST['degree']);

    if(empty($name) || empty($email) || empty($password_raw) || empty($phone) || empty($college) || empty($degree)){
        $message = "All fields are required!";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = "Invalid email format!";
    }
    elseif(strlen($password_raw) < 6){
        $message = "Password must be at least 6 characters!";
    }
    else {
        // Prepare statement to check email
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $message = "Email already registered!";
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            
            // Start Transaction
            $conn->begin_transaction();

            try {
                // Upload Resume
                $resume_path = NULL;
                if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0){
                    $allowed = ['pdf', 'doc', 'docx'];
                    $filename = $_FILES['resume']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $filesize = $_FILES['resume']['size'];

                    if(!in_array($ext, $allowed)){
                        throw new Exception("Invalid file type! Only PDF, DOC, DOCX allowed.");
                    }
                    if($filesize > 2097152){ // 2MB
                        throw new Exception("File too large! Max 2MB.");
                    }

                    $new_name = uniqid() . "_resume." . $ext;
                    $upload_dir = '../uploads/resumes/';
                    if(!is_dir($upload_dir)){
                        mkdir($upload_dir, 0777, true);
                    }
                    $destination = $upload_dir . $new_name;

                    if(move_uploaded_file($_FILES['resume']['tmp_name'], $destination)){
                        $resume_path = 'uploads/resumes/' . $new_name;
                    } else {
                        throw new Exception("Failed to upload resume.");
                    }
                }

                // Insert User
                $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt1->bind_param("sss", $name, $email, $password);
                
                if (!$stmt1->execute()) {
                    throw new Exception("Error inserting user: " . $stmt1->error);
                }
                $user_id = $conn->insert_id;

                // Insert Student Profile
                $stmt2 = $conn->prepare("INSERT INTO students (user_id, phone, college, degree, resume_path) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("issss", $user_id, $phone, $college, $degree, $resume_path);
                
                if (!$stmt2->execute()) {
                    throw new Exception("Error inserting student profile: " . $stmt2->error);
                }

                $conn->commit();
                $message = "Student registered successfully! <a href='student_login.php'>Login here</a>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Registration failed: " . $e->getMessage();
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="glass-panel auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Join thousands of students launching their careers.</p>
            </div>
            
            <?php if(isset($message) && $message) echo "<div class='alert' style='background: rgba(16, 185, 129, 0.1); color: var(--secondary); padding: 10px; border-radius: 8px; margin-bottom: 20px;'>$message</div>"; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password (min 6 chars)" required>
                </div>
                <div class="form-group">
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number (10 digits)" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <input type="text" name="college" class="form-control" placeholder="College Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="degree" class="form-control" placeholder="Degree / Major" required>
                    </div>
                </div>
                <!-- Resume Upload -->
                <div class="form-group" style="margin-top: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: var(--gray); font-size: 0.9rem;">Upload Resume (PDF/DOC, Max 2MB)</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" style="padding: 10px;">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            </form>
            
            <p class="mt-4" style="color: var(--gray);">
                Already have an account? <a href="student_login.php" style="color: var(--primary); font-weight: 600;">Login here</a>
            </p>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
