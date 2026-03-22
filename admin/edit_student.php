<?php
session_start();
require '../db/db.php';

// Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// Get student ID from URL
if(!isset($_GET['id'])){
    header("Location: manage_students.php");
    exit();
}
$student_id = intval($_GET['id']);

// Fetch existing student data
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$student_id AND role='student'");
if(mysqli_num_rows($result) == 0){
    header("Location: manage_students.php");
    exit();
}
$student = mysqli_fetch_assoc($result);

// Handle form submission
$success = "";
$error = "";
if(isset($_POST['update'])){
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    if($name == "" || $email == ""){
        $error = "All fields are required!";
    } else {
        // Update student info (without phone)
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='student'");
        $stmt->bind_param("ssi", $name, $email, $student_id);
        if($stmt->execute()){
            $success = "Student info updated successfully!";
            // Refresh student data
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$student_id AND role='student'");
            $student = mysqli_fetch_assoc($result);
        } else {
            $error = "Database error: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial; background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 220px; position: fixed; background-color: #343a40; color: white; padding-top: 20px; }
        .sidebar a { color: white; display: block; padding: 10px 20px; text-decoration: none; }
        .sidebar a:hover { background-color: #495057; border-radius: 5px; }
        .main-content { margin-left: 240px; padding: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center">Admin Panel</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_students.php">Students</a>
    <a href="manage_companies.php">Companies</a>
    <a href="manage_internships.php">Internships</a>
    <a href="view_applications.php">Applications</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Edit Student</h2>

    <?php if($error != "") { echo "<div class='alert alert-danger'>$error</div>"; } ?>
    <?php if($success != "") { echo "<div class='alert alert-success'>$success</div>"; } ?>

    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update Student</button>
        <a href="manage_students.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
