<?php
session_start();
require '../db/db.php';

// ✅ Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Fetch summary counts
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"))['total'];
$total_companies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM companies"))['total'];
$total_internships = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM internships"))['total'];
$total_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM applications"))['total'];
$active_internships = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM internships WHERE status='active'"))['total'];
$pending_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM applications WHERE status='pending'"))['total'];

// ✅ Recent registrations
$recent_students = mysqli_query($conn, "
    SELECT u.name, u.email, u.created_at, s.college
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.role = 'student'
    ORDER BY u.created_at DESC
    LIMIT 5
");

$recent_companies = mysqli_query($conn, "
    SELECT u.name, u.email, u.created_at, c.company_name, c.industry
    FROM users u
    JOIN companies c ON u.id = c.user_id
    WHERE u.role = 'company'
    ORDER BY u.created_at DESC
    LIMIT 5
");

// ✅ Recent applications
$recent_applications = mysqli_query($conn, "
    SELECT 
        a.id,
        a.status,
        a.applied_on,
        i.title AS internship_title,
        u_student.name AS student_name,
        u_company.name AS company_name
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    JOIN students s ON a.student_id = s.id
    JOIN users u_student ON s.user_id = u_student.id
    JOIN companies c ON i.company_id = c.id
    JOIN users u_company ON c.user_id = u_company.id
    ORDER BY a.applied_on DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | InternPortal</title>
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
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_students.php">Manage Students</a>
        <a href="manage_companies.php">Manage Companies</a>
        <a href="manage_internships.php">Manage Internships</a>
        <a href="view_applications.php">View Applications</a>
        <a href="reports.php">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">System Overview</h2>
        
        <!-- Stats Grid -->
        <div class="stat-grid">
            <div class="glass-panel stat-card">
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_companies; ?></h3>
                <p>Total Companies</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $active_internships; ?></h3>
                <p>Active Internships</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_applications; ?></h3>
                <p>Total Applications</p>
            </div>
        </div>

        <!-- Pending Actions Alert -->
        <?php if($pending_applications > 0): ?>
        <div class="alert" style="background: rgba(245, 158, 11, 0.1); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.2); margin-bottom: 1.5rem;">
            ⚠️ You have <strong><?php echo $pending_applications; ?></strong> pending application(s) that need review.
            <a href="manage_internships.php" style="color: #D97706; text-decoration: underline; margin-left: 0.5rem;">Review Now</a>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="glass-panel mb-4">
            <h3>Quick Actions</h3>
            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                <a href="manage_students.php" class="btn btn-primary">👥 Manage Students</a>
                <a href="manage_companies.php" class="btn btn-primary">🏢 Manage Companies</a>
                <a href="manage_internships.php" class="btn btn-primary">📋 Manage Internships</a>
                <a href="view_applications.php" class="btn btn-secondary">📄 View Applications</a>
                <a href="reports.php" class="btn btn-secondary">📊 Generate Reports</a>
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
                            <th>ID</th>
                            <th>Student</th>
                            <th>Company</th>
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
                            <td>#<?php echo $app['id']; ?></td>
                            <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['internship_title']); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($app['applied_on'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <p>No applications yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Registrations -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
            <!-- Recent Students -->
            <div class="glass-panel" style="padding: 0; overflow: hidden;">
                <div style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-bottom: 1px solid var(--glass-border);">
                    <h3 style="margin: 0; font-size: 1.25rem;">Recent Student Registrations</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if(mysqli_num_rows($recent_students) > 0): ?>
                        <?php while($student = mysqli_fetch_assoc($recent_students)): ?>
                        <div style="padding: 1rem; background: rgba(255,255,255,0.5); border-radius: 8px; margin-bottom: 0.75rem;">
                            <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($student['name']); ?></div>
                            <div style="font-size: 0.9rem; color: var(--gray);"><?php echo htmlspecialchars($student['college']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-top: 0.25rem;">
                                📧 <?php echo htmlspecialchars($student['email']); ?>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-top: 0.25rem;">
                                📅 <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--gray);">No recent registrations.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Companies -->
            <div class="glass-panel" style="padding: 0; overflow: hidden;">
                <div style="padding: 1.5rem; background: rgba(245, 158, 11, 0.05); border-bottom: 1px solid var(--glass-border);">
                    <h3 style="margin: 0; font-size: 1.25rem;">Recent Company Registrations</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if(mysqli_num_rows($recent_companies) > 0): ?>
                        <?php while($company = mysqli_fetch_assoc($recent_companies)): ?>
                        <div style="padding: 1rem; background: rgba(255,255,255,0.5); border-radius: 8px; margin-bottom: 0.75rem;">
                            <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($company['company_name'] ?: $company['name']); ?></div>
                            <div style="font-size: 0.9rem; color: var(--gray);"><?php echo htmlspecialchars($company['industry'] ?: 'Not specified'); ?></div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-top: 0.25rem;">
                                📧 <?php echo htmlspecialchars($company['email']); ?>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-top: 0.25rem;">
                                📅 <?php echo date('M d, Y', strtotime($company['created_at'])); ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--gray);">No recent registrations.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
