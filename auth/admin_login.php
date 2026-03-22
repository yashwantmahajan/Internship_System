<?php
session_start();
require '../db/db.php'; // DB connection

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Fetch admin user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // ✅ Set consistent session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['name'] = $user['name'];

                // Redirect to admin dashboard
                header("Location: ../admin/admin_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Admin not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar container" style="padding: 1.5rem 2rem;">
        <a href="../index.php" class="logo" style="font-size: 1.5rem; font-weight: 700; color: var(--primary); text-decoration: none;">
            🎓 InternHub Pro
        </a>
        <div class="nav-links" style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="../index.php" style="color: var(--gray); text-decoration: none; font-weight: 500; transition: color 0.3s;">Home</a>
            <div style="position: relative; display: inline-block;">
                <button class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;" onclick="toggleRoleMenu()">
                    Switch Role ▼
                </button>
                <div id="roleMenu" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border-radius: var(--radius-md); box-shadow: var(--glass-shadow); min-width: 180px; z-index: 1000;">
                    <a href="student_login.php" style="display: block; padding: 0.75rem 1rem; color: var(--dark); text-decoration: none; border-bottom: 1px solid var(--glass-border);">👨‍🎓 Student</a>
                    <a href="company_login.php" style="display: block; padding: 0.75rem 1rem; color: var(--dark); text-decoration: none; border-bottom: 1px solid var(--glass-border);">🏢 Company</a>
                    <a href="admin_login.php" style="display: block; padding: 0.75rem 1rem; color: var(--primary); text-decoration: none; font-weight: 600;">⚙️ Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="glass-panel auth-card">
            <div class="auth-header">
                <h2 style="color: var(--primary);">⚙️ Admin Portal</h2>
                <p>Restricted Access</p>
            </div>
            
            <?php if($error) echo "<div class='alert' style='color: var(--danger); background: rgba(239,68,68,0.1); padding: 10px; border-radius: 8px; margin-bottom: 15px;'>$error</div>"; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Admin Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="background: var(--dark); border-color: var(--dark);">Login</button>
            </form>
            
        </div>
    </div>
    
    <script>
    function toggleRoleMenu() {
        const menu = document.getElementById('roleMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('roleMenu');
        const button = event.target.closest('button');
        if (!button && menu.style.display === 'block') {
            menu.style.display = 'none';
        }
    });
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
