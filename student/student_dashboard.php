<?php  
session_start();
require '../db/db.php';

// ✅ Student session check
if(!isset($_SESSION['student_id'])){
    header("Location: ../auth/student_login.php");
    exit;
}

$student_user_id = intval($_SESSION['student_id']); // user_id of student (from users table)

// ✅ Fetch student info to get correct student.id
$student_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM students WHERE user_id = $student_user_id"));
if(!$student_data){
    die("Student profile not found!");
}
$student_id = $student_data['id']; // this is actual students.id used in applications table

// ✅ Fetch student profile details
$profile = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT u.name, u.email, s.phone, s.college, s.degree 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE s.id = $student_id
"));

// ✅ Fetch internships applied by this student
$query = mysqli_query($conn, "
    SELECT 
        a.id AS app_id, 
        i.title AS internship_title, 
        i.payment_type, 
        i.stipend,
        a.status, 
        a.applied_on, 
        a.completed_at, 
        a.certificate_issued, 
        a.certificate_path, 
        u.name AS company_name
    FROM applications a
    JOIN internships i ON a.internship_id = i.id
    JOIN companies c ON i.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE a.student_id = $student_id
    ORDER BY a.applied_on DESC
");

// ✅ Count summaries
$total_applied = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = $student_id"))['total'];
$total_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = $student_id AND status = 'completed'"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = $student_id AND status IN ('pending','approved')"))['total'];
$total_certificates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM applications WHERE student_id = $student_id AND certificate_issued = 'yes'"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Student Panel</h4>
        <a href="student_dashboard.php" class="active">Dashboard</a>
        <a href="apply_internship.php">Apply Internships</a>
        <a href="student_profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($profile['name']); ?>!</h2>

        <!-- Stats -->
        <div class="stat-grid">
            <div class="glass-panel stat-card">
                <h3><?php echo $total_applied; ?></h3>
                <p>Applied</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_completed; ?></h3>
                <p>Completed</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_pending; ?></h3>
                <p>In Progress</p>
            </div>
            <div class="glass-panel stat-card">
                <h3><?php echo $total_certificates; ?></h3>
                <p>Certificates</p>
            </div>
        </div>


        <!-- Recommendations -->
        <div class="glass-panel mb-4" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-bottom: 1px solid var(--glass-border);">
                <h3 style="margin: 0; font-size: 1.25rem; color: var(--secondary);">Recommended for You</h3>
                <p style="margin: 0; font-size: 0.9rem; color: var(--gray);">Based on your degree: <strong><?php echo htmlspecialchars($profile['degree']); ?></strong></p>
            </div>
            <div style="padding: 1.5rem;">
                <?php
                // ✅ Simple recommendation logic: Match degree keywords in title or description
                $degree_keywords = explode(' ', $profile['degree']);
                $like_clauses = [];
                foreach($degree_keywords as $word){
                    if(strlen($word) > 2) { // Ignore short words
                        $safe_word = mysqli_real_escape_string($conn, $word);
                        $like_clauses[] = "(title LIKE '%$safe_word%' OR description LIKE '%$safe_word%')";
                    }
                }
                
                if(empty($like_clauses)) {
                    $recommendation_query = "SELECT * FROM internships WHERE status='active' ORDER BY posted_on DESC LIMIT 3";
                } else {
                    $where_clause = implode(' OR ', $like_clauses);
                    $recommendation_query = "SELECT * FROM internships WHERE status='active' AND ($where_clause) ORDER BY posted_on DESC LIMIT 3";
                }

                $rec_result = mysqli_query($conn, $recommendation_query);

                if(mysqli_num_rows($rec_result) > 0):
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">';
                    while($rec = mysqli_fetch_assoc($rec_result)):
                ?>
                    <div style="background: white; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid #E2E8F0;">
                        <h4 style="margin-bottom: 0.5rem; color: var(--primary);"><?php echo htmlspecialchars($rec['title']); ?></h4>
                        <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 0.5rem;"><?php echo htmlspecialchars(substr($rec['description'], 0, 80)) . '...'; ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge badge-completed" style="background: #EEF2FF; color: var(--primary);"><?php echo $rec['payment_type']; ?></span>
                            <a href="apply_internship.php?id=<?php echo $rec['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.9rem;">Apply</a>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                    echo '</div>';
                else: 
                ?>
                    <p style="color: var(--gray);">No specific recommendations found. <a href="apply_internship.php" style="color: var(--primary);">Browse all internships</a>.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table -->
        <div class="glass-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; background: rgba(79, 70, 229, 0.05); border-bottom: 1px solid var(--glass-border);">
                <h3 style="margin: 0; font-size: 1.25rem;">Recent Applications</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Internship</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) > 0) { 
                            while($row = mysqli_fetch_assoc($query)) { 
                                $status = !empty($row['status']) ? strtolower($row['status']) : 'pending';
                                $cert_status = !empty($row['certificate_issued']) ? strtolower($row['certificate_issued']) : 'no';
                                $badgeClass = 'badge-' . $status;
                            ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['internship_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td>
                                <?php echo $row['payment_type']; ?>
                                <?php if($row['payment_type']=='Paid') echo "<span style='font-size:0.8em; color:var(--gray); display:block;'>₹".$row['stipend']."</span>"; ?>
                            </td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($row['applied_on'])); ?></td>
                            <td>
                                <?php 
                                if($cert_status=='yes' && !empty($row['certificate_path'])){
                                    echo '<a href="../'.$row['certificate_path'].'" target="_blank" class="btn btn-primary" style="padding: 4px 10px; font-size: 0.8rem; background: var(--secondary);">🏆 View Certificate</a>';
                                } else {
                                    echo '<span style="color: var(--gray); font-size: 0.9rem;">-</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr><td colspan="6" class="text-center" style="padding: 3rem; color: var(--gray);">No applications yet. Start applying!</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
