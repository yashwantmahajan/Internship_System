<?php
session_start();
require '../db/db.php';

// Admin session check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/admin_login.php");
    exit;
}

// Initialize date range
$from = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // first day of current month
$to   = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');       // today

// Fetch counts
$student_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM users 
     WHERE role='student' AND created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'"))['total'];

$company_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM users 
     WHERE role='company' AND created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'"))['total'];

$internship_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM internships 
     WHERE posted_on BETWEEN '$from 00:00:00' AND '$to 23:59:59'"))['total'];

$application_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM applications 
     WHERE applied_on BETWEEN '$from 00:00:00' AND '$to 23:59:59'"))['total'];

// Get total counts (all time)
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='student'"))['total'];
$total_companies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='company'"))['total'];
$total_internships = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM internships"))['total'];
$total_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM applications"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports | InternPortal</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-header {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            text-align: center;
        }
        .date-filter {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .date-filter .form-group {
            margin-bottom: 0;
        }
        .stats-comparison {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-compare-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
        }
        .stat-compare-card h4 {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0 0 1rem 0;
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-main {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }
        .stat-subtitle {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-md);
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Admin Panel</h4>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_students.php">Manage Students</a>
        <a href="manage_companies.php">Manage Companies</a>
        <a href="manage_internships.php">Manage Internships</a>
        <a href="view_applications.php">View Applications</a>
        <a href="reports.php" class="active">Reports</a>
        <a href="settings.php">Settings</a>
        <a href="../auth/logout.php" style="margin-top: auto; color: var(--danger) !important;">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="report-header">
            <h1 style="margin: 0; font-size: 2rem;">📊 System Reports</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Analyze system activity and growth</p>
        </div>

        <!-- Date Filter -->
        <div class="glass-panel mb-4">
            <h3 style="margin-bottom: 1.5rem;">📅 Date Range Filter</h3>
            <form method="GET" class="date-filter">
                <div class="form-group">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?php echo $from; ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?php echo $to; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </form>
        </div>

        <!-- Statistics Comparison -->
        <h3 style="margin-bottom: 1.5rem;">📈 Activity Summary</h3>
        <div class="stats-comparison">
            <div class="stat-compare-card" style="border-left: 4px solid #3b82f6;">
                <h4>👥 Students</h4>
                <div class="stat-main" style="color: #3b82f6;"><?php echo $student_count; ?></div>
                <div class="stat-subtitle">
                    in selected period<br>
                    <strong><?php echo $total_students; ?></strong> total
                </div>
            </div>

            <div class="stat-compare-card" style="border-left: 4px solid #10b981;">
                <h4>🏢 Companies</h4>
                <div class="stat-main" style="color: #10b981;"><?php echo $company_count; ?></div>
                <div class="stat-subtitle">
                    in selected period<br>
                    <strong><?php echo $total_companies; ?></strong> total
                </div>
            </div>

            <div class="stat-compare-card" style="border-left: 4px solid #f59e0b;">
                <h4>📋 Internships</h4>
                <div class="stat-main" style="color: #f59e0b;"><?php echo $internship_count; ?></div>
                <div class="stat-subtitle">
                    in selected period<br>
                    <strong><?php echo $total_internships; ?></strong> total
                </div>
            </div>

            <div class="stat-compare-card" style="border-left: 4px solid #ef4444;">
                <h4>📄 Applications</h4>
                <div class="stat-main" style="color: #ef4444;"><?php echo $application_count; ?></div>
                <div class="stat-subtitle">
                    in selected period<br>
                    <strong><?php echo $total_applications; ?></strong> total
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="glass-panel mb-4" style="padding: 0; overflow: hidden;">
            <div style="padding: 1.5rem; background: rgba(79, 70, 229, 0.05); border-bottom: 1px solid var(--glass-border);">
                <h3 style="margin: 0; font-size: 1.25rem;">📊 Detailed Breakdown</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="table" style="margin: 0; border: none; box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Period Count</th>
                            <th>Total Count</th>
                            <th>Percentage</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>👥 Students</strong></td>
                            <td><?php echo $student_count; ?></td>
                            <td><?php echo $total_students; ?></td>
                            <td>
                                <?php 
                                $student_percent = $total_students > 0 ? round(($student_count / $total_students) * 100, 1) : 0;
                                echo $student_percent . '%';
                                ?>
                            </td>
                            <td>
                                <a href="manage_students.php" class="btn btn-sm btn-info">View All</a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>🏢 Companies</strong></td>
                            <td><?php echo $company_count; ?></td>
                            <td><?php echo $total_companies; ?></td>
                            <td>
                                <?php 
                                $company_percent = $total_companies > 0 ? round(($company_count / $total_companies) * 100, 1) : 0;
                                echo $company_percent . '%';
                                ?>
                            </td>
                            <td>
                                <a href="manage_companies.php" class="btn btn-sm btn-info">View All</a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>📋 Internships</strong></td>
                            <td><?php echo $internship_count; ?></td>
                            <td><?php echo $total_internships; ?></td>
                            <td>
                                <?php 
                                $internship_percent = $total_internships > 0 ? round(($internship_count / $total_internships) * 100, 1) : 0;
                                echo $internship_percent . '%';
                                ?>
                            </td>
                            <td>
                                <a href="manage_internships.php" class="btn btn-sm btn-info">View All</a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>📄 Applications</strong></td>
                            <td><?php echo $application_count; ?></td>
                            <td><?php echo $total_applications; ?></td>
                            <td>
                                <?php 
                                $application_percent = $total_applications > 0 ? round(($application_count / $total_applications) * 100, 1) : 0;
                                echo $application_percent . '%';
                                ?>
                            </td>
                            <td>
                                <a href="view_applications.php" class="btn btn-sm btn-info">View All</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <h3 style="margin-bottom: 1.5rem;">📈 Visual Comparison</h3>
            <canvas id="reportChart" height="100"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('reportChart').getContext('2d');
const reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Students','Companies','Internships','Applications'],
        datasets: [{
            label: 'Selected Period',
            data: [<?php echo $student_count; ?>, <?php echo $company_count; ?>, <?php echo $internship_count; ?>, <?php echo $application_count; ?>],
            backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444']
        }, {
            label: 'Total (All Time)',
            data: [<?php echo $total_students; ?>, <?php echo $total_companies; ?>, <?php echo $total_internships; ?>, <?php echo $total_applications; ?>],
            backgroundColor: ['rgba(59, 130, 246, 0.3)','rgba(16, 185, 129, 0.3)','rgba(245, 158, 11, 0.3)','rgba(239, 68, 68, 0.3)']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { 
                display: true,
                position: 'top'
            },
            title: { 
                display: true, 
                text: 'Activity Comparison: Selected Period vs All Time',
                font: {
                    size: 16,
                    weight: 'bold'
                }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
