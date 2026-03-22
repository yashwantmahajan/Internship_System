<?php 
session_start();
require '../db/db.php';

// ✅ Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit();
}

// ✅ Add new company
if(isset($_POST['add_company'])){
    $company_name = trim($_POST['company_name']);
    $full_name    = trim($_POST['full_name']);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role         = 'company';

    // Check duplicate email
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "Company with this email already exists!";
    } else {
        // Step 1: Insert into users table
        $insert_user = mysqli_query($conn, "INSERT INTO users (name, email, password, role) VALUES ('$full_name', '$email', '$password', '$role')");
        if($insert_user){
            $user_id = mysqli_insert_id($conn);

            // Step 2: Insert into companies table
            $insert_company = mysqli_query($conn, "INSERT INTO companies (user_id, company_name, phone) VALUES ($user_id, '$company_name', '$phone')");
            if($insert_company){
                $success = "Company added successfully!";
            } else {
                $error = "Error adding company details: " . mysqli_error($conn);
            }
        } else {
            $error = "Error adding company user: " . mysqli_error($conn);
        }
    }
}

// ✅ Delete company (users + companies)
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM companies WHERE user_id=$id");
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='company'");
    header("Location: manage_companies.php");
    exit();
}

// ✅ Fetch companies with proper company name and contact person
$companies = mysqli_query($conn, "
    SELECT u.id, c.company_name, u.name AS contact_person, u.email, c.phone
    FROM users u
    LEFT JOIN companies c ON u.id = c.user_id
    WHERE u.role='company'
    ORDER BY u.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Companies - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Manage Companies</h2>

    <!-- ✅ Add Company Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Add New Company
        </div>
        <div class="card-body">
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" name="company_name" class="form-control" placeholder="Company Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="full_name" class="form-control" placeholder="Contact Person Name" required>
                    </div>
                    <div class="col-md-2">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="phone" class="form-control" placeholder="Phone" required>
                    </div>
                    <div class="col-md-2">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>
                <button type="submit" name="add_company" class="btn btn-success">Add Company</button>
            </form>
        </div>
    </div>

    <!-- ✅ Company Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Company Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($companies)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['company_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_person'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $row['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this company?')">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
            <?php if(mysqli_num_rows($companies) == 0) echo "<tr><td colspan='6' class='text-center'>No companies found</td></tr>"; ?>
        </tbody>
    </table>

    <a href="./admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
