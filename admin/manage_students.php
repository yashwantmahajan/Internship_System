<?php
session_start();
require '../db/db.php';

// ✅ Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Delete student
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    // Delete from students table first
    mysqli_query($conn, "DELETE FROM students WHERE user_id=$id");
    // Then delete from users table
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='student'");
    header("Location: manage_students.php");
    exit();
}

// ✅ Search functionality
$search = "";
if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $students = mysqli_query($conn, "
        SELECT u.id, u.name, u.email, u.role, s.phone
        FROM users u
        LEFT JOIN students s ON u.id = s.user_id
        WHERE u.role='student' AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%')
        ORDER BY u.id DESC
    ");
} else {
    $students = mysqli_query($conn, "
        SELECT u.id, u.name, u.email, u.role, s.phone
        FROM users u
        LEFT JOIN students s ON u.id = s.user_id
        WHERE u.role='student'
        ORDER BY u.id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
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
    <a href="manage_students.php" class="active">Students</a>
    <a href="manage_companies.php">Companies</a>
    <a href="manage_internships.php">Internships</a>
    <a href="view_applications.php">Applications</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Manage Students</h2>

    <!-- Search Form -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- Students Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($students)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                    <td><?php echo $row['role']; ?></td>
                    <td>
                        <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="manage_students.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
            <?php if(mysqli_num_rows($students) == 0) echo "<tr><td colspan='6' class='text-center'>No students found</td></tr>"; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
