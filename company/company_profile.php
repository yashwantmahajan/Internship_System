<?php
session_start();
require '../db/db.php';

// Ensure company is logged in
if(!isset($_SESSION['company_id'])){
    header("Location: ../auth/company_login.php");
    exit;
}

$company_id = intval($_SESSION['company_id']);
$error = "";
$success = "";

// Fetch company profile along with user info
$query = "
    SELECT u.id AS user_id, u.name AS full_name, u.email, c.*
    FROM users u
    JOIN companies c ON u.id = c.user_id
    WHERE c.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    die("Company profile not found. Please contact admin.");
}

// Form submission
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $company_name = trim($_POST['company_name'] ?? '');
    $full_name    = trim($_POST['full_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $city         = trim($_POST['city'] ?? '');
    $industry     = trim($_POST['industry'] ?? '');
    $website      = trim($_POST['website'] ?? '');
    $founded_year = intval($_POST['founded_year'] ?? 0);
    $team_size    = trim($_POST['team_size'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $twitter_url  = trim($_POST['twitter_url'] ?? '');

    // Check if email is used by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $profile['user_id']);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $error = "This email is already used by another company!";
    } else {
        $stmt->close();

        // Update users table (full_name and email)
        $stmt_user = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt_user->bind_param("ssi", $full_name, $email, $profile['user_id']);
        $stmt_user->execute();
        $stmt_user->close();

        // Update companies table
        $stmt_company = $conn->prepare("
            UPDATE companies SET
                company_name = ?,
                full_name = ?,
                email = ?,
                phone = ?,
                address = ?,
                city = ?,
                industry = ?,
                website = ?,
                founded_year = ?,
                team_size = ?,
                description = ?,
                linkedin_url = ?,
                twitter_url = ?
            WHERE id = ?
        ");
        $stmt_company->bind_param("ssssssssissssi", 
            $company_name, $full_name, $email, $phone, $address, $city, 
            $industry, $website, $founded_year, $team_size, $description, 
            $linkedin_url, $twitter_url, $company_id
        );
        
        if($stmt_company->execute()){
            $success = "Profile updated successfully!";
            // Refresh profile data
            $profile['company_name'] = $company_name;
            $profile['full_name'] = $full_name;
            $profile['email'] = $email;
            $profile['phone'] = $phone;
            $profile['address'] = $address;
            $profile['city'] = $city;
            $profile['industry'] = $industry;
            $profile['website'] = $website;
            $profile['founded_year'] = $founded_year;
            $profile['team_size'] = $team_size;
            $profile['description'] = $description;
            $profile['linkedin_url'] = $linkedin_url;
            $profile['twitter_url'] = $twitter_url;

            $_SESSION['company_name'] = $company_name;
        } else {
            $error = "Database error: " . $stmt_company->error;
        }
        $stmt_company->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Profile | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            text-align: center;
        }
        .company-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: #10b981;
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
            border-bottom: 2px solid var(--secondary);
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
        .input-with-icon input,
        .input-with-icon select {
            padding-left: 2.5rem;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            z-index: 1;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Company Panel</h4>
        <a href="company_dashboard.php">Dashboard</a>
        <a href="post_internship.php">Post Internship</a>
        <a href="my_internships.php">My Internships</a>
        <a href="company_profile.php" class="active">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="company-logo">
                <?php echo strtoupper(substr($profile['company_name'], 0, 1)); ?>
            </div>
            <h2 style="margin: 0; font-size: 2rem;"><?php echo htmlspecialchars($profile['company_name']); ?></h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1.1rem;">
                <?php echo htmlspecialchars($profile['industry'] ?? 'Company Profile'); ?>
            </p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                ✅ <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Company Information -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>🏢</span> Company Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($profile['company_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Industry <span class="text-danger">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">🏭</span>
                            <select name="industry" class="form-control" required>
                                <option value="">Select Industry</option>
                                <?php
                                $industries = ['Technology', 'Finance', 'Healthcare', 'Education', 'E-commerce', 'Manufacturing', 'Consulting', 'Marketing', 'Real Estate', 'Retail', 'Telecommunications', 'Transportation', 'Media', 'Hospitality', 'Other'];
                                foreach($industries as $ind):
                                    $selected = ($profile['industry'] == $ind) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $ind; ?>" <?php echo $selected; ?>><?php echo $ind; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Founded Year</label>
                        <div class="input-with-icon">
                            <span class="input-icon">📅</span>
                            <input type="number" name="founded_year" class="form-control" value="<?php echo $profile['founded_year'] ?? ''; ?>" min="1800" max="<?php echo date('Y'); ?>" placeholder="e.g., 2015">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Team Size</label>
                        <div class="input-with-icon">
                            <span class="input-icon">👥</span>
                            <select name="team_size" class="form-control">
                                <option value="">Select Team Size</option>
                                <?php
                                $sizes = ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'];
                                foreach($sizes as $size):
                                    $selected = (($profile['team_size'] ?? '') == $size) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $size; ?>" <?php echo $selected; ?>><?php echo $size; ?> employees</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Company Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Tell us about your company, mission, and culture..."><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>📞</span> Contact Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">📧</span>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div class="input-with-icon">
                            <span class="input-icon">📱</span>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>" placeholder="+91 XXXXXXXXXX">
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
                                    $selected = (($profile['city'] ?? '') == $city) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $city; ?>" <?php echo $selected; ?>><?php echo $city; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Office Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Enter complete office address..."><?php echo htmlspecialchars($profile['address']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Online Presence -->
            <div class="glass-panel mb-4">
                <h3 class="section-title">
                    <span>🌐</span> Online Presence
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Company Website</label>
                        <div class="input-with-icon">
                            <span class="input-icon">🌐</span>
                            <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>" placeholder="https://yourcompany.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">LinkedIn Page</label>
                        <div class="input-with-icon">
                            <span class="input-icon">💼</span>
                            <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter Profile</label>
                        <div class="input-with-icon">
                            <span class="input-icon">🐦</span>
                            <input type="url" name="twitter_url" class="form-control" value="<?php echo htmlspecialchars($profile['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/yourcompany">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                💾 Update Profile
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
