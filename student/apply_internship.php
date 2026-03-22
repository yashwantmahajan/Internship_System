<?php
session_start();
require '../db/db.php';

if(!isset($_SESSION['student_id'])){
    header("Location: ../auth/student_login.php");
    exit();
}

$student_user_id = intval($_SESSION['student_id']);
$student_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM students WHERE user_id = $student_user_id"));
if(!$student_row){ die("Student record not found!"); }
$student_id = $student_row['id'];

$message = "";
$msg_type = "";

// ✅ Handle Apply with CV Upload (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['internship_id'])){
    $internship_id = intval($_POST['internship_id']);

    // Check duplicate
    $check = mysqli_query($conn, "SELECT id FROM applications WHERE student_id=$student_id AND internship_id=$internship_id");
    if(mysqli_num_rows($check) > 0){
        $message = "You have already applied for this internship!";
        $msg_type = "warning";
    } else {
        $cv_path = NULL;

        // Handle CV Upload
        if(isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK){
            $allowed = ['pdf','doc','docx'];
            $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)){
                $message = "Only PDF, DOC, DOCX files are allowed for CV.";
                $msg_type = "danger";
            } elseif($_FILES['cv']['size'] > 5 * 1024 * 1024){
                $message = "CV must be under 5MB.";
                $msg_type = "danger";
            } else {
                $upload_dir = '../uploads/cvs/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $filename = 'cv_' . $student_id . '_' . $internship_id . '_' . time() . '.' . $ext;
                $dest = $upload_dir . $filename;
                if(move_uploaded_file($_FILES['cv']['tmp_name'], $dest)){
                    $cv_path = 'uploads/cvs/' . $filename;
                } else {
                    $message = "Failed to upload CV. Please try again.";
                    $msg_type = "danger";
                }
            }
        }

        if(empty($message)){
            $cv_safe = $cv_path ? mysqli_real_escape_string($conn, $cv_path) : NULL;
            if($cv_safe){
                $insert = mysqli_query($conn, "INSERT INTO applications (student_id, internship_id, status, applied_on, created_at, certificate_issued, cv_path) VALUES ($student_id, $internship_id, 'pending', NOW(), NOW(), 'no', '$cv_safe')");
            } else {
                $insert = mysqli_query($conn, "INSERT INTO applications (student_id, internship_id, status, applied_on, created_at, certificate_issued) VALUES ($student_id, $internship_id, 'pending', NOW(), NOW(), 'no')");
            }
            if($insert){
                $message = "Applied successfully! The company will review your application.";
                $msg_type = "success";
            } else {
                $message = "Error: ".mysqli_error($conn);
                $msg_type = "danger";
            }
        }
    }
}

// Handle filters
$location_filter = isset($_GET['location']) && $_GET['location'] != '' ? $_GET['location'] : '';
$search_query   = isset($_GET['search'])   && $_GET['search']   != '' ? $_GET['search']   : '';

// Build query
$query_str = "
    SELECT i.id, i.title, i.description, i.location, i.start_date, i.end_date,
           i.payment_type, i.stipend, i.status, u.name AS company_name
    FROM internships i
    JOIN companies c ON i.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE i.status = 'active'
";
if($location_filter){
    $lf = mysqli_real_escape_string($conn, $location_filter);
    $query_str .= " AND i.location = '$lf'";
}
if($search_query){
    $sq = mysqli_real_escape_string($conn, $search_query);
    $query_str .= " AND (i.title LIKE '%$sq%' OR i.description LIKE '%$sq%' OR u.name LIKE '%$sq%')";
}
$query_str .= " ORDER BY i.posted_on DESC";
$internships = mysqli_query($conn, $query_str);

$locations_query = mysqli_query($conn, "SELECT DISTINCT location FROM internships WHERE location != '' AND location IS NOT NULL ORDER BY location");

// Get IDs of internships student already applied to
$applied_ids = [];
$applied_res = mysqli_query($conn, "SELECT internship_id FROM applications WHERE student_id=$student_id");
while($ar = mysqli_fetch_assoc($applied_res)) $applied_ids[] = $ar['internship_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Internship | InternPortal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:16px; padding:2rem; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.2); animation: slideUp 0.3s ease; }
        @keyframes slideUp { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }
        .modal-box h3 { margin-bottom:1.5rem; font-size:1.25rem; }
        .file-upload-area { border:2px dashed var(--primary); border-radius:10px; padding:2rem; text-align:center; cursor:pointer; transition:background 0.2s; }
        .file-upload-area:hover { background:rgba(79,70,229,0.05); }
        .file-upload-area input[type=file] { display:none; }
        .applied-badge { background:#D1FAE5; color:#059669; padding:6px 14px; border-radius:20px; font-size:0.85rem; font-weight:600; }
    </style>
</head>
<body>

<?php if(!empty($message)): ?>
<!-- Toast Notification -->
<div id="toast" class="toast toast-<?= $msg_type ?> show">
    <?= htmlspecialchars($message) ?>
    <button onclick="document.getElementById('toast').remove()" style="background:none;border:none;float:right;font-size:1.2rem;cursor:pointer;color:inherit; margin-left:1rem;">×</button>
</div>
<style>
.toast{position:fixed;top:1.5rem;right:1.5rem;z-index:99999;padding:1rem 1.5rem;border-radius:10px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.15);max-width:400px;}
.toast-success{background:#D1FAE5;color:#065F46;}
.toast-warning{background:#FEF3C7;color:#92400E;}
.toast-danger{background:#FEE2E2;color:#991B1B;}
</style>
<?php endif; ?>

<!-- Apply Modal -->
<div class="modal-overlay" id="applyModal">
    <div class="modal-box">
        <h3>📄 Apply for <span id="modalTitle"></span></h3>
        <form method="POST" enctype="multipart/form-data" id="applyForm">
            <input type="hidden" name="internship_id" id="modalInternshipId">
            <div class="file-upload-area" onclick="document.getElementById('cvFile').click()">
                <div style="font-size:2rem;">📎</div>
                <p style="font-weight:600; margin:0.5rem 0;">Upload Your CV</p>
                <p style="color:var(--gray); font-size:0.9rem; margin:0;">PDF, DOC, DOCX · Max 5MB</p>
                <input type="file" name="cv" id="cvFile" accept=".pdf,.doc,.docx" onchange="updateFileName(this)">
            </div>
            <p id="fileName" style="color:var(--primary); font-size:0.9rem; margin-top:0.75rem; font-weight:500;"></p>
            <p style="color:var(--gray); font-size:0.85rem; margin:0.5rem 0 1.5rem;">CV upload is optional but recommended. You can still apply without one.</p>
            <div style="display:flex;gap:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Submit Application</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Student Panel</h4>
        <a href="student_dashboard.php">Dashboard</a>
        <a href="apply_internship.php" class="active">Apply Internships</a>
        <a href="student_profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Available Internships</h2>

        <!-- Filter Bar -->
        <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label>Search</label>
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search by title, company..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                <div class="filter-group" style="flex: 0 0 200px;">
                    <label>Location</label>
                    <select name="location" class="form-control">
                        <option value="">All Cities</option>
                        <?php while($loc = mysqli_fetch_assoc($locations_query)):
                            $sel = ($location_filter == $loc['location']) ? 'selected' : ''; ?>
                            <option value="<?= htmlspecialchars($loc['location']) ?>" <?= $sel ?>><?= htmlspecialchars($loc['location']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="apply_internship.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <?php if(mysqli_num_rows($internships) > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php while($row = mysqli_fetch_assoc($internships)):
                    $already_applied = in_array($row['id'], $applied_ids);
                ?>
                    <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h3 style="font-size: 1.25rem; color: var(--primary); margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h3>
                                <p style="color: var(--dark); font-weight: 600;">
                                    🏢 <?= htmlspecialchars($row['company_name']) ?>
                                </p>
                            </div>
                            <span style="background: <?= $row['payment_type']=='Paid'?'#D1FAE5':'#FEF3C7' ?>; color: <?= $row['payment_type']=='Paid'?'#059669':'#D97706' ?>; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; white-space:nowrap;">
                                <?= $row['payment_type'] ?>
                            </span>
                        </div>
                        
                        <p style="color: var(--gray); font-size: 0.95rem; margin-bottom: 1rem; flex: 1;">
                            <?= htmlspecialchars(substr($row['description'], 0, 120)) ?>...
                        </p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.9rem; color: var(--gray); margin-bottom: 1.5rem;">
                            <div>📍 <?= htmlspecialchars($row['location']) ?></div>
                            <div>💰 <?= ($row['payment_type']=='Paid') ? "₹".$row['stipend'] : "Unpaid" ?></div>
                            <div>📅 <?= $row['start_date'] ? date('M d, Y', strtotime($row['start_date'])) : 'TBD' ?></div>
                            <div>⏳ <?= $row['end_date'] ? date('M d, Y', strtotime($row['end_date'])) : 'TBD' ?></div>
                        </div>

                        <?php if($already_applied): ?>
                            <span class="applied-badge" style="text-align:center; display:block;">✅ Already Applied</span>
                        <?php else: ?>
                            <button class="btn btn-primary" style="text-align: center;" onclick="openModal(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['title'])) ?>')">Apply Now</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-panel text-center" style="padding: 3rem;">
                <p style="color: var(--gray); font-size: 1.1rem;">No internships available currently. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openModal(id, title) {
    document.getElementById('modalInternshipId').value = id;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('applyModal').classList.add('open');
}
function closeModal() {
    document.getElementById('applyModal').classList.remove('open');
}
function updateFileName(input) {
    const p = document.getElementById('fileName');
    p.textContent = input.files[0] ? '📎 ' + input.files[0].name : '';
}
document.getElementById('applyModal').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>
