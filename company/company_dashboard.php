<?php
session_start();
require '../db/db.php';

// ✅ Ensure both session values exist
if (!isset($_SESSION['company_user_id'], $_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

// ✅ Use session company_id directly
$company_id = intval($_SESSION['company_id']); // companies.id

// ✅ Fetch summary data
$total_posted = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM internships WHERE company_id=$company_id"))['total'];
$total_applicants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM applications a JOIN internships i ON a.internship_id=i.id WHERE i.company_id=$company_id"))['total'];
$total_active = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM internships WHERE company_id=$company_id AND status='active'"))['total'];
$total_certificates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM applications a JOIN internships i ON a.internship_id=i.id WHERE i.company_id=$company_id AND a.certificate_issued='yes'"))['total'];

// ✅ Fetch recent internships with stats
$internships_query = mysqli_query($conn, "
    SELECT 
        i.id, 
        i.title, 
        i.location,
        i.status, 
        i.posted_on,
        i.payment_type,
        i.stipend,
        COUNT(a.id) AS total_applicants,
        SUM(CASE WHEN a.status='pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN a.status='approved' THEN 1 ELSE 0 END) AS approved_count
    FROM internships i
    LEFT JOIN applications a ON i.id = a.internship_id
    WHERE i.company_id = $company_id
    GROUP BY i.id
    ORDER BY i.posted_on DESC
    LIMIT 10
");

// ✅ Fetch recent applications
$recent_applications = mysqli_query($conn, "
    SELECT 
        a.id,
        a.status,
        a.applied_on,
        i.title AS internship_title,
        u.name AS student_name,
        s.college
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE i.company_id = $company_id
    ORDER BY a.applied_on DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Dashboard | InternPortal</title>
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
        <a href="company_dashboard.php" class="active">Dashboard</a>
        <a href="post_internship.php">Post Internship</a>
        <a href="my_internships.php">My Internships</a>
        <a href="company_profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Company Overview</h2>

        <!-- Stats -->
        <div class="stat-grid">
            <div class="glass-panel stat-card">
                <h3><?php echo $total_posted; ?></h3>
                <p>Total Internships</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_active; ?></h3>
                <p>Active Internships</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_applicants; ?></h3>
                <p>Total Applicants</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_certificates; ?></h3>
                <p>Issued Certificates</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-panel mb-4">
            <h3>Quick Actions</h3>
            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                <a href="post_internship.php" class="btn btn-primary">📝 Post New Internship</a>
                <a href="my_internships.php" class="btn btn-secondary">📋 View All Internships</a>
                <a href="company_profile.php" class="btn btn-secondary">⚙️ Update Profile</a>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="glass-panel mb-4" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; background: rgba(79, 70, 229, 0.05); border-bottom: 1px solid var(--glass-border);">
                <h3 style="margin: 0; font-size: 1.25rem;">Recent Applications</h3>
            </div>
            <div style="overflow-x: auto;">
                <?php if(mysqli_num_rows($recent_applications) > 0): ?>
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>College</th>
                            <th>Internship</th>
                            <th>Status</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($app = mysqli_fetch_assoc($recent_applications)): 
                            $status = strtolower($app['status']);
                            $badgeClass = 'badge-' . $status;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['college']); ?></td>
                            <td><?php echo htmlspecialchars($app['internship_title']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($app['applied_on'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <p>No applications yet. Post internships to receive applications!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Internships Overview -->
        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-bottom: 1px solid var(--glass-border);">
                <h3 style="margin: 0; font-size: 1.25rem;">My Internships</h3>
            </div>
            <div style="overflow-x: auto;">
                <?php if(mysqli_num_rows($internships_query) > 0): ?>
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Applicants</th>
                            <th>Pending</th>
                            <th>Approved</th>
                            <th>Posted On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($int = mysqli_fetch_assoc($internships_query)): 
                            $status = strtolower($int['status']);
                            $statusBadge = 'badge-' . $status;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($int['title']); ?></strong></td>
                            <td>📍 <?php echo htmlspecialchars($int['location']); ?></td>
                            <td>
                                <?php if($int['payment_type'] == 'Paid'): ?>
                                    <span style="color: var(--secondary);">💰 ₹<?php echo number_format($int['stipend']); ?></span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($status); ?></span></td>
                            <td><strong><?php echo $int['total_applicants']; ?></strong></td>
                            <td><?php echo $int['pending_count']; ?></td>
                            <td><?php echo $int['approved_count']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($int['posted_on'])); ?></td>
                            <td>
                                <a href="view_applicants.php?internship_id=<?php echo $int['id']; ?>" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <p>You haven't posted any internships yet.</p>
                    <a href="post_internship.php" class="btn btn-primary" style="margin-top: 1rem;">Post Your First Internship</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
