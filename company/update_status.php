<?php
session_start();
require '../db/db.php';

// ✅ Session check
if (!isset($_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

// ✅ Check parameters
if (isset($_GET['app_id']) && isset($_GET['status']) && isset($_GET['internship_id'])) {
    $app_id = intval($_GET['app_id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $internship_id = intval($_GET['internship_id']);

    // ✅ Update applicant status
    mysqli_query($conn, "UPDATE applications SET status='$status' WHERE id=$app_id");

    header("Location: view_applicants.php?internship_id=$internship_id");
    exit;
} else {
    header("Location: company_dashboard.php");
    exit;
}
?>
