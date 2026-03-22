<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About - Internship Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        .features {
            padding: 50px 0;
        }
        .feature-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .feature-box:hover {
            transform: translateY(-5px);
        }
        .feature-box h4 {
            margin-bottom: 20px;
            color: #007bff;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>About Internship Management System</h1>
    <p>Connecting Students and Companies Efficiently</p>
</div>

<!-- Features Section -->
<div class="container features">
    <div class="row text-center">
        <!-- For Students -->
        <div class="col-md-4">
            <div class="feature-box">
                <h4>For Students</h4>
                <ul class="list-unstyled">
                    <li>✅ Apply for internships online</li>
                    <li>✅ Track application status</li>
                    <li>✅ Download certificates</li>
                    <li>✅ View company profiles</li>
                </ul>
            </div>
        </div>

        <!-- For Companies -->
        <div class="col-md-4">
            <div class="feature-box">
                <h4>For Companies</h4>
                <ul class="list-unstyled">
                    <li>✅ Post new internships</li>
                    <li>✅ View applicants</li>
                    <li>✅ Issue certificates to students</li>
                    <li>✅ Manage internships dashboard</li>
                </ul>
            </div>
        </div>

        <!-- General Features -->
        <div class="col-md-4">
            <div class="feature-box">
                <h4>General Features</h4>
                <ul class="list-unstyled">
                    <li>✅ Secure login for all roles</li>
                    <li>✅ Dashboard summaries</li>
                    <li>✅ Responsive and clean UI</li>
                    <li>✅ Easy to maintain</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4" style="background-color: #007bff; color:white;">
    &copy; <?php echo date("Y"); ?> Internship Management System. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
