<?php
session_start();
require '../db/db.php';

// ✅ Check if logged in
if(!isset($_SESSION['student_id'])){
    header("Location: ../auth/student_login.php");
    exit();
}

$user_id = intval($_SESSION['student_id']); // This is users.id
$message = "";
$messageType = "";

// Get student_id from students table
$student_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM students WHERE user_id = $user_id"));
if(!$student_row){
    die("Student profile not found!");
}
$student_id = $student_row['id'];

// ✅ Handle Profile Update
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $college = trim($_POST['college']);
    $degree = trim($_POST['degree']);
    $city = trim($_POST['city'] ?? '');
    $year_of_study = trim($_POST['year_of_study'] ?? '');
    $percentage = floatval($_POST['percentage'] ?? 0);
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $portfolio_url = trim($_POST['portfolio_url'] ?? '');

    // Handle Resume Upload
    $resume_update_query = "";
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['resume']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = "resume_" . $user_id . "_" . time() . "." . $ext;
            $upload_dir = "../uploads/resumes/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $new_filename)) {
                $resume_path = "uploads/resumes/" . $new_filename;
                $conn->query("UPDATE students SET resume_path='$resume_path' WHERE user_id=$user_id");
            }
        }
    }

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $user_id);
    $update_user = $stmt->execute();
    $stmt->close();
    
    // Update students table
    $stmt = $conn->prepare("UPDATE students SET phone=?, college=?, degree=?, city=?, year_of_study=?, percentage=?, skills=?, bio=?, linkedin_url=?, github_url=?, portfolio_url=? WHERE user_id=?");
    $stmt->bind_param("sssssdsssssi", $phone, $college, $degree, $city, $year_of_study, $percentage, $skills, $bio, $linkedin_url, $github_url, $portfolio_url, $user_id);
    $update_student = $stmt->execute();
    $stmt->close();

    if($update_user && $update_student){
        $message = "Profile updated successfully!";
        $messageType = "success";
        $_SESSION['student_name'] = $name;
    } else {
        $message = "Error updating profile: ".mysqli_error($conn);
        $messageType = "danger";
    }
}

// ✅ Fetch current student info
$query = mysqli_query($conn, "
    SELECT u.name, u.email, s.* 
    FROM users u 
    JOIN students s ON u.id = s.user_id 
    WHERE u.id = $user_id
");

if(mysqli_num_rows($query) == 0){
    die("Student profile not found! Please contact admin.");
}

$student = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255,255,255,0.3);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon input {
            padding-left: 2.5rem;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
        }
        .skill-tag {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0.25rem;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Student Panel</h4>
        <a href="student_dashboard.php">Dashboard</a>
        <a href="apply_internship.php">Apply Internships</a>
        <a href="student_profile.php" class="active">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
            </div>
            <h2 style="margin: 0; font-size: 2rem;"><?php echo htmlspecialchars($student['name']); ?></h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1.1rem;">
                <?php echo htmlspecialchars($student['degree'] ?? 'Student'); ?> 
                <?php if(!empty($student['college'])): ?>
                    @ <?php echo htmlspecialchars($student['college']); ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 1.5rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>👤</span> Basic Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address (Read Only)</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" readonly style="background: rgba(0,0,0,0.05); cursor: not-allowed;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">📱</span>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">City <span class="text-danger">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">📍</span>
                            <select name="city" class="form-control" required>
                                <option value="">Select City</option>
                                <?php
                                $cities = ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Pune', 'Chennai', 'Kolkata', 'Ahmedabad', 'Jaipur', 'Surat', 'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Patna', 'Vadodara', 'Ghaziabad', 'Ludhiana', 'Agra', 'Nashik', 'Faridabad', 'Meerut', 'Rajkot', 'Varanasi', 'Srinagar', 'Aurangabad', 'Dhanbad', 'Amritsar', 'Navi Mumbai', 'Allahabad', 'Ranchi', 'Howrah', 'Coimbatore', 'Jabalpur', 'Gwalior', 'Vijayawada', 'Jodhpur', 'Madurai', 'Raipur', 'Kota', 'Other'];
                                foreach($cities as $city):
                                    $selected = (($student['city'] ?? '') == $city) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $city; ?>" <?php echo $selected; ?>><?php echo $city; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education Details -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>🎓</span> Education Details
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">College / University <span class="text-danger">*</span></label>
                        <input type="text" name="college" class="form-control" value="<?php echo htmlspecialchars($student['college']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Degree / Major <span class="text-danger">*</span></label>
                        <input type="text" name="degree" class="form-control" value="<?php echo htmlspecialchars($student['degree']); ?>" required placeholder="e.g., B.Tech in Computer Science">
                        <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">
                            * We use your degree to recommend relevant internships
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Year of Study</label>
                        <select name="year_of_study" class="form-control">
                            <option value="">Select Year</option>
                            <?php
                            $years = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Final Year', 'Graduated'];
                            foreach($years as $year):
                                $selected = (($student['year_of_study'] ?? '') == $year) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $year; ?>" <?php echo $selected; ?>><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Percentage / CGPA</label>
                        <div class="input-with-icon">
                            <span class="input-icon">📊</span>
                            <input type="number" name="percentage" class="form-control" value="<?php echo $student['percentage'] ?? ''; ?>" step="0.01" min="0" max="100" placeholder="e.g., 85.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills & Bio -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>💡</span> Skills & About
                </h3>
                <div class="form-group">
                    <label class="form-label">Skills</label>
                    <input type="text" name="skills" class="form-control" value="<?php echo htmlspecialchars($student['skills'] ?? ''); ?>" placeholder="e.g., Python, Java, React, Machine Learning">
                    <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">
                        Separate skills with commas
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio / About Me</label>
                    <textarea name="bio" class="form-control" rows="4" placeholder="Tell us about yourself, your interests, and career goals..."><?php echo htmlspecialchars($student['bio'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Social Links -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>🔗</span> Social & Portfolio Links
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">LinkedIn Profile</label>
                        <div class="input-with-icon">
                            <span class="input-icon">💼</span>
                            <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($student['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">GitHub Profile</label>
                        <div class="input-with-icon">
                            <span class="input-icon">💻</span>
                            <input type="url" name="github_url" class="form-control" value="<?php echo htmlspecialchars($student['github_url'] ?? ''); ?>" placeholder="https://github.com/yourusername">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Portfolio Website</label>
                        <div class="input-with-icon">
                            <span class="input-icon">🌐</span>
                            <input type="url" name="portfolio_url" class="form-control" value="<?php echo htmlspecialchars($student['portfolio_url'] ?? ''); ?>" placeholder="https://yourportfolio.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resume Upload -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>📄</span> Resume
                </h3>
                <div class="form-group">
                    <label class="form-label">Upload New Resume (PDF, DOC, DOCX)</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                    <?php if(!empty($student['resume_path'])): ?>
                        <p style="margin-top: 10px; font-size: 0.9rem;">
                            Current Resume: <a href="../<?php echo $student['resume_path']; ?>" target="_blank" style="color: var(--primary); font-weight: 500;">View File</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                💾 Save Profile
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
