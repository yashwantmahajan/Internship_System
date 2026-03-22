<?php  
session_start();
require '../db/db.php';

// ✅ Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Fetch applications with student and company names
$applications = mysqli_query($conn, "
    SELECT 
        a.id AS app_id,
        u_student.name AS student_name,
        i.title AS internship_title,
        u_company.name AS company_name,
        a.status,
        a.applied_on
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u_student ON s.user_id = u_student.id
    JOIN internships i ON a.internship_id = i.id
    JOIN companies c ON i.company_id = c.id
    JOIN users u_company ON c.user_id = u_company.id
    ORDER BY a.applied_on DESC
");

// ✅ Debug check (optional)
// if(!$applications){
//     die("Query Error: ".mysqli_error($conn));
// }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Applications - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>All Applications</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Internship</th>
                <th>Company</th>
                <th>Status</th>
                <th>Applied On</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($applications)) { ?>
            <tr>
                <td><?php echo $row['app_id']; ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['internship_title']); ?></td>
                <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['applied_on'])); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
