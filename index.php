<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Management System</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar container">
        <a href="index.php" class="logo">InternPortal</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="auth/student_login.php" class="btn btn-secondary" style="padding: 8px 16px; border-radius: 6px;">Login</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero container">
        <div class="hero-content">
            <span style="color: var(--primary); font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Launch Your Career</span>
            <h1>Find Your Dream <br> Internship Today.</h1>
            <p>Connect with top companies and kickstart your professional journey. Simple, fast, and efficient internship management.</p>
            <div style="display: flex; gap: 1rem;">
                <a href="auth/register_student.php" class="btn btn-primary">Get Started</a>
                <a href="auth/company_login.php" class="btn btn-secondary">Post Internship</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="glass-panel" style="width: 100%; max-width: 400px; text-align: center;">
                <h3 style="margin-bottom: 1rem;">Choose Your Role</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="auth/student_login.php" class="btn btn-primary" style="width: 100%;">Student Login</a>
                    <a href="auth/company_login.php" class="btn btn-secondary" style="width: 100%;">Company Login</a>
                    <a href="auth/admin_login.php" style="font-size: 0.9rem; color: var(--gray); margin-top: 0.5rem;">Admin Access</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section (New) -->
    <section class="container" style="padding: 4rem 0;">
        <div class="glass-panel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; border: none; background: white;">
            <div class="text-center">
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;">For Students</h3>
                <p style="color: var(--gray);">Apply to verified internships, track status, and get certified.</p>
            </div>
            <div class="text-center">
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">For Companies</h3>
                <p style="color: var(--gray);">Post vacancies, review applicants, and manage hiring easily.</p>
            </div>
            <div class="text-center">
                <h3 style="color: var(--accent); margin-bottom: 0.5rem;">Seamless Process</h3>
                <p style="color: var(--gray);">From application to certification, everything in one dashboard.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="text-align: center; padding: 2rem; color: var(--gray); font-size: 0.9rem;">
        &copy; 2025 Internship Management System. All Rights Reserved.
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
