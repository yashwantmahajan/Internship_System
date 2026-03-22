<?php
session_start();
require '../db/db.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/admin_login.php");
    exit;
}

$message = "";
$messageType = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['admin_id'];

    if(empty($current_password) || empty($new_password) || empty($confirm_password)){
        $message = "All fields are required.";
        $messageType = "danger";
    } elseif($new_password !== $confirm_password){
        $message = "New passwords do not match.";
        $messageType = "danger";
    } elseif(strlen($new_password) < 6){
        $message = "New password must be at least 6 characters.";
        $messageType = "danger";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if($user && password_verify($current_password, $user['password'])){
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hash, $user_id);
            
            if($update->execute()){
                $message = "Password updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating password.";
                $messageType = "danger";
            }
        } else {
            $message = "Incorrect current password.";
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Admin Portal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_students.php">Students</a>
        <a href="manage_companies.php">Companies</a>
        <a href="manage_internships.php">Internships</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php" class="active">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Admin Settings</h2>

        <div class="glass-panel" style="max-width: 600px;">
            <h3 class="mb-3">Change Password</h3>
            
            <?php if($message): ?>
                <div class="alert" style="padding: 10px; border-radius: 8px; margin-bottom: 20px; 
                    background: <?php echo $messageType == 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; 
                    color: <?php echo $messageType == 'success' ? 'var(--secondary)' : 'var(--danger)'; ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

        <div class="glass-panel mt-4" style="max-width: 600px;">
            <h3 class="mb-3">Appearance</h3>
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="checkbox">
                    <input type="checkbox" id="checkbox" />
                    <div class="slider round"></div>
                </label>
                <em style="margin-left: 10px; font-style: normal; font-weight: 500;">Dark Mode</em>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
