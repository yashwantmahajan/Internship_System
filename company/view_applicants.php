<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

$company_id = intval($_SESSION['company_id']);

if (!isset($_GET['internship_id'])) {
    header("Location: company_dashboard.php");
    exit;
}
$internship_id = intval($_GET['internship_id']);

// Fetch internship — ensure it belongs to this company
$internship = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT title FROM internships WHERE id=$internship_id AND company_id=$company_id
"));
if (!$internship) {
    die("❌ Invalid Internship or Access Denied!");
}

// ✅ FIXED JOIN: go through students table to get user info
$applicants_query = mysqli_query($conn, "
    SELECT a.id AS app_id, a.status, a.certificate_issued, a.cv_path,
           u.name, u.email, s.phone, s.college, s.degree, s.resume_path
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE a.internship_id = $internship_id
    ORDER BY a.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicants — <?= htmlspecialchars($internship['title']) ?> | InternPortal</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Company Panel</h4>
        <a href="company_dashboard.php">Dashboard</a>
        <a href="post_internship.php">Post Internship</a>
        <a href="my_internships.php" class="active">My Internships</a>
        <a href="company_profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top:auto; color:var(--danger)!important;">Logout</a>
    </div>

    <div class="main-content">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:2rem; flex-wrap:wrap;">
            <a href="my_internships.php" class="btn btn-secondary" style="padding:8px 16px;">← Back</a>
            <h2 style="margin:0;">Applicants: <?= htmlspecialchars($internship['title']) ?></h2>
        </div>

        <?php if(mysqli_num_rows($applicants_query) > 0): ?>
        <div class="glass-panel" style="padding:0; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="table" style="margin:0; border:none; box-shadow:none;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>College / Degree</th>
                            <th>CV</th>
                            <th>Status</th>
                            <th>Certificate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i=1; while($row = mysqli_fetch_assoc($applicants_query)):
                        $status   = strtolower($row['status'] ?? 'pending');
                        $cert     = strtolower($row['certificate_issued'] ?? 'no');
                        $badge = [
                            'pending'   => 'badge-pending',
                            'approved'  => 'badge-approved',
                            'rejected'  => 'badge-rejected',
                            'completed' => 'badge-completed',
                        ][$status] ?? 'badge-pending';
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                        <td style="font-size:0.9rem;"><?= htmlspecialchars($row['email']) ?></td>
                        <td style="font-size:0.85rem; color:var(--gray);">
                            <?= htmlspecialchars($row['college']) ?><br>
                            <em><?= htmlspecialchars($row['degree']) ?></em>
                        </td>
                        <td>
                            <?php 
                            $cv_link = !empty($row['resume_path']) ? '../'.$row['resume_path'] : (!empty($row['cv_path']) ? '../'.$row['cv_path'] : '');
                            if(!empty($cv_link)): ?>
                                <a href="<?= htmlspecialchars($cv_link) ?>" target="_blank" class="btn btn-primary" style="padding:4px 12px; font-size:0.8rem;">📄 View Resume</a>
                            <?php else: ?>
                                <span style="color:var(--gray); font-size:0.85rem;">No Resume</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span></td>
                        <td>
                            <?php if($cert === 'yes'): ?>
                                <span class="badge badge-completed">✅ Issued</span>
                            <?php else: ?>
                                <span style="color:var(--gray); font-size:0.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                                <?php if($status !== 'approved' && $status !== 'completed'): ?>
                                <a href="update_status.php?app_id=<?= $row['app_id'] ?>&status=approved&internship_id=<?= $internship_id ?>"
                                   class="btn btn-primary" style="padding:4px 10px; font-size:0.8rem;"
                                   onclick="return confirm('Approve this applicant?')">✅ Approve</a>
                                <?php endif; ?>
                                <?php if($status !== 'rejected' && $status !== 'completed'): ?>
                                <a href="update_status.php?app_id=<?= $row['app_id'] ?>&status=rejected&internship_id=<?= $internship_id ?>"
                                   class="btn btn-secondary" style="padding:4px 10px; font-size:0.8rem; color:var(--danger); border-color:var(--danger);"
                                   onclick="return confirm('Reject this applicant?')">❌ Reject</a>
                                <?php endif; ?>
                                <?php if($status === 'approved' && $cert !== 'yes'): ?>
                                <a href="issue_certificate.php?app_id=<?= $row['app_id'] ?>&internship_id=<?= $internship_id ?>"
                                   class="btn btn-primary" style="padding:4px 10px; font-size:0.8rem; background:var(--secondary);"
                                   onclick="return confirm('Issue certificate to this student?')">🏆 Issue Cert</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="glass-panel text-center" style="padding:3rem;">
                <p style="color:var(--gray); font-size:1.1rem;">No applicants yet for this internship.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
