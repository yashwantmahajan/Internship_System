<?php
session_start();
require '../db/db.php';

// ✅ Company login check
if (!isset($_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

$company_id = intval($_SESSION['company_id']);
$error = "";
$success = "";

// ✅ Fetch company profile
$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$company) {
    die("Company profile not found. Please update your profile first.");
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = 'active'; // ✅ always active
    $location = trim($_POST['location']);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $payment_type = $_POST['payment_type'] ?? 'Unpaid';
    $stipend = !empty($_POST['stipend']) ? $_POST['stipend'] : 0;

    if (empty($title)) {
        $error = "Title is required!";
    } else {
        // Prepare statement with correct number of bind variables
        $stmt = $conn->prepare("
            INSERT INTO internships 
            (company_id, title, description, status, posted_on, location, start_date, end_date, payment_type, stipend)
            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");

        // Bind parameters
        $stmt->bind_param(
            "isssssssd",
            $company_id,
            $title,
            $description,
            $status,
            $location,
            $start_date,
            $end_date,
            $payment_type,
            $stipend
        );

        if ($stmt->execute()) {
            $success = "✅ Internship posted successfully!";
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Internship | InternPortal</title>
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
        <a href="post_internship.php" class="active">Post Internship</a>
        <a href="my_internships.php">My Internships</a>
        <a href="company_profile.php">Profile</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="mb-4">Post New Internship</h2>
        
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

        <div class="glass-panel" style="max-width: 800px;">
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Job Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Software Engineer Intern">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Describe the role and requirements..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Location/City <span class="text-danger">*</span></label>
                    <select name="location" class="form-control" required>
                        <option value="">Select City</option>
                        <option value="Remote">Remote</option>
                        <option value="Mumbai">Mumbai</option>
                        <option value="Delhi">Delhi</option>
                        <option value="Bangalore">Bangalore</option>
                        <option value="Hyderabad">Hyderabad</option>
                        <option value="Pune">Pune</option>
                        <option value="Chennai">Chennai</option>
                        <option value="Kolkata">Kolkata</option>
                        <option value="Ahmedabad">Ahmedabad</option>
                        <option value="Jaipur">Jaipur</option>
                        <option value="Surat">Surat</option>
                        <option value="Lucknow">Lucknow</option>
                        <option value="Kanpur">Kanpur</option>
                        <option value="Nagpur">Nagpur</option>
                        <option value="Indore">Indore</option>
                        <option value="Thane">Thane</option>
                        <option value="Bhopal">Bhopal</option>
                        <option value="Visakhapatnam">Visakhapatnam</option>
                        <option value="Pimpri-Chinchwad">Pimpri-Chinchwad</option>
                        <option value="Patna">Patna</option>
                        <option value="Vadodara">Vadodara</option>
                        <option value="Ghaziabad">Ghaziabad</option>
                        <option value="Ludhiana">Ludhiana</option>
                        <option value="Agra">Agra</option>
                        <option value="Nashik">Nashik</option>
                        <option value="Faridabad">Faridabad</option>
                        <option value="Meerut">Meerut</option>
                        <option value="Rajkot">Rajkot</option>
                        <option value="Kalyan-Dombivali">Kalyan-Dombivali</option>
                        <option value="Vasai-Virar">Vasai-Virar</option>
                        <option value="Varanasi">Varanasi</option>
                        <option value="Srinagar">Srinagar</option>
                        <option value="Aurangabad">Aurangabad</option>
                        <option value="Dhanbad">Dhanbad</option>
                        <option value="Amritsar">Amritsar</option>
                        <option value="Navi Mumbai">Navi Mumbai</option>
                        <option value="Allahabad">Allahabad</option>
                        <option value="Ranchi">Ranchi</option>
                        <option value="Howrah">Howrah</option>
                        <option value="Coimbatore">Coimbatore</option>
                        <option value="Jabalpur">Jabalpur</option>
                        <option value="Gwalior">Gwalior</option>
                        <option value="Vijayawada">Vijayawada</option>
                        <option value="Jodhpur">Jodhpur</option>
                        <option value="Madurai">Madurai</option>
                        <option value="Raipur">Raipur</option>
                        <option value="Kota">Kota</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Internship Type</label>
                        <select name="payment_type" id="payment_type" class="form-control" onchange="toggleStipend()">
                            <option value="Unpaid">Unpaid</option>
                            <option value="Paid">Paid</option>
                        </select>
                    </div>
                    <div class="form-group" id="stipend_field" style="display:none;">
                        <label class="form-label">Stipend Amount (₹)</label>
                        <input type="number" name="stipend" min="0" step="100" class="form-control" placeholder="0.00">
                    </div>
                </div>

                <button class="btn btn-primary mt-4">Post Internship</button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
function toggleStipend(){
    const type = document.getElementById("payment_type").value;
    const field = document.getElementById("stipend_field");
    if(type === "Paid"){
        field.style.display = "block";
    } else {
        field.style.display = "none";
    }
}
</script>
</body>
</html>
