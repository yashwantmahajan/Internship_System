<?php
session_start();
require '../db/db.php';

// Company login check
if (!isset($_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

$company_id = intval($_SESSION['company_id']);

// Fetch all internships with applicant and certificate counts
$stmt = $conn->prepare("
    SELECT 
        i.*, 
        COUNT(a.id) AS total_applicants, 
        SUM(CASE WHEN a.certificate_issued='yes' THEN 1 ELSE 0 END) AS total_certificates,
        SUM(CASE WHEN a.status='pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN a.status='approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN a.status='completed' THEN 1 ELSE 0 END) AS completed_count
    FROM internships i
    LEFT JOIN applications a ON i.id = a.internship_id
    WHERE i.company_id = ?
    GROUP BY i.id
    ORDER BY i.posted_on DESC
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$internships = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Internships | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
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
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">My Posted Internships</h2>
            <a href="post_internship.php" class="btn btn-primary">+ Post New Internship</a>
        </div>

        <?php if($internships->num_rows > 0): ?>
        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Applicants</th>
                            <th>Pending</th>
                            <th>Approved</th>
                            <th>Completed</th>
                            <th>Certificates</th>
                            <th>Posted On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i=1; 
                        while($row = $internships->fetch_assoc()):
                            $status = strtolower(trim($row['status']));
                            $badge_class = match($status) {
                                'active' => 'badge-active',
                                'inactive' => 'badge-inactive',
                                'closed' => 'badge-closed',
                                default => 'badge-inactive'
                            };
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td>📍 <?php echo htmlspecialchars($row['location'] ?? 'Not specified'); ?></td>
                            <td>
                                <?php if($row['payment_type'] == 'Paid'): ?>
                                    <span style="color: var(--secondary); font-weight: 500;">💰 ₹<?php echo number_format($row['stipend']); ?></span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span></td>
                            <td><strong><?php echo $row['total_applicants']; ?></strong></td>
                            <td><?php echo $row['pending_count']; ?></td>
                            <td><?php echo $row['approved_count']; ?></td>
                            <td><?php echo $row['completed_count']; ?></td>
                            <td><?php echo $row['total_certificates']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['posted_on'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="view_applicants.php?internship_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="glass-panel empty-state">
            <h3 style="color: var(--gray);">No Internships Posted Yet</h3>
            <p>You haven't posted any internships yet. Start by posting your first internship!</p>
            <a href="post_internship.php" class="btn btn-primary" style="margin-top: 1rem;">Post Your First Internship</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
