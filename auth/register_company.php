<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db/db.php'; // Make sure this file defines $conn correctly

$message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Trim inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // --- Validation ---
    if(empty($name) || empty($email) || empty($password_raw) || empty($phone) || empty($company_name) || empty($location)){
        $message = "All fields are required!";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = "Invalid email format!";
    }
    elseif(strlen($password_raw) < 6){
        $message = "Password must be at least 6 characters!";
    }
    elseif(!preg_match('/^[0-9]{10}$/', $phone)){
        $message = "Phone must be 10 digits!";
    }
    else{
        // Hash password
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        // --- Check if email already exists ---
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $message = "Email already registered!";
        } else {

            // ✅ STOP execution if somehow email is empty
            if (empty($email)) {
                die("<p style='color:red;'>❌ ERROR: Email field is empty before insert. Please check your form input name='email'.</p>");
            }

            // --- Insert into users table ---
            $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'company')");
            $stmt1->bind_param("sss", $name, $email, $password);

            if($stmt1->execute()){
                $user_id = $stmt1->insert_id;

                // ✅ Fix: use correct column name (change 'address' to 'location' if DB uses 'location')
                $stmt2 = $conn->prepare("INSERT INTO companies (user_id, phone, company_name, address) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("isss", $user_id, $phone, $company_name, $location);

                if($stmt2->execute()){
                    $message = "✅ Company registered successfully!";
                } else {
                    $message = "Error inserting into companies: " . $stmt2->error;
                }

                $stmt2->close();
            } else {
                $message = "Error inserting into users: " . $stmt1->error;
            }

            $stmt1->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Registration | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="glass-panel auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <h2 style="color: var(--secondary);">Partner With Us</h2>
                <p>Find the best talent for your organization.</p>
            </div>
            
            <?php if(isset($message) && $message) echo "<div class='alert' style='background: rgba(16, 185, 129, 0.1); color: var(--secondary); padding: 10px; border-radius: 8px; margin-bottom: 20px;'>$message</div>"; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Contact Person Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Work Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password (min 6 chars)" required>
                </div>
                <div class="form-group">
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="text" name="company_name" class="form-control" placeholder="Company Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="location" class="form-control" placeholder="Location / Address" required>
                </div>
                
                <button type="submit" class="btn btn-secondary w-100" style="color: white; background: var(--secondary); border: none;">Register Company</button>
            </form>
            
            <p class="mt-4" style="color: var(--gray);">
                Already registered? <a href="company_login.php" style="color: var(--secondary); font-weight: 600;">Login here</a>
            </p>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>
