<?php
session_start();
require '../db/db.php';

// ✅ Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Handle actions
if(isset($_GET['approve_id'])){
    $id = intval($_GET['approve_id']);
    mysqli_query($conn, "UPDATE applications SET status='approved' WHERE id=$id");
    header("Location: manage_internships.php"); exit();
}
if(isset($_GET['reject_id'])){
    $id = intval($_GET['reject_id']);
    mysqli_query($conn, "UPDATE applications SET status='rejected' WHERE id=$id");
    header("Location: manage_internships.php"); exit();
}
if(isset($_GET['complete_id'])){
    $id = intval($_GET['complete_id']);
    mysqli_query($conn, "UPDATE applications SET status='completed', completed_at=NOW() WHERE id=$id");
    header("Location: manage_internships.php"); exit();
}
if(isset($_GET['issue_cert_id'])){
    $id = intval($_GET['issue_cert_id']);
    $cert_path = 'certificates/cert_'.$id.'.pdf';
    mysqli_query($conn, "UPDATE applications SET certificate_issued='yes', certificate_path='$cert_path' WHERE id=$id");
    header("Location: manage_internships.php"); exit();
}

// ✅ Fetch applications with correct student info
$query = mysqli_query($conn, "
    SELECT a.id AS app_id, a.status, a.certificate_issued, a.certificate_path, a.applied_on,
           u.name AS student_name,
           i.title AS internship_title,
           i.location,
           c.company_name AS company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN internships i ON a.internship_id = i.id
    JOIN companies c ON i.company_id = c.id
    ORDER BY a.applied_on DESC
");

if(!$query){ die("Query Error: ".mysqli_error($conn)); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Internships - Admin | InternPortal</title>
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
        <a href="manage_students.php">Manage Students</a>
        <a href="manage_companies.php">Manage Companies</a>
        <a href="manage_internships.php" class="active">Manage Internships</a>
        <a href="view_applications.php">View Applications</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Manage Internship Applications</h2>

        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Company</th>
                            <th>Internship</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Certificate</th>
                            <th>Applied On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($row=mysqli_fetch_assoc($query)) { 
                        $status = strtolower(trim($row['status'] ?? 'pending'));
                        $cert_status = strtolower(trim($row['certificate_issued'] ?? 'no'));
                        $badgeClass = 'badge-' . $status;
                    ?>
                    <tr>
                        <td>#<?php echo $row['app_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['student_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['company_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['internship_title'] ?? '-'); ?></td>
                        <td>📍 <?php echo htmlspecialchars($row['location'] ?? 'N/A'); ?></td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span></td>
                        <td>
                            <?php 
                            if($cert_status=='yes') echo '<span class="badge badge-approved">Issued</span>';
                            else echo '<span class="badge badge-inactive">Not Issued</span>';
                            ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['applied_on'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                            <?php
                            if($status=='pending'){ ?>
                                <a href="?approve_id=<?php echo $row['app_id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <a href="?reject_id=<?php echo $row['app_id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                            <?php } elseif($status=='approved'){ ?>
                                <a href="?complete_id=<?php echo $row['app_id']; ?>" class="btn btn-sm btn-info">Complete</a>
                            <?php } elseif($status=='completed' && $cert_status!='yes'){ ?>
                                <a href="?issue_cert_id=<?php echo $row['app_id']; ?>" class="btn btn-sm btn-warning">Issue Cert</a>
                            <?php } else { echo "-"; } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-4">⬅ Back to Dashboard</a>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
