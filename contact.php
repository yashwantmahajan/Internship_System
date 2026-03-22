<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - Internship Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        .contact-section {
            padding: 50px 0;
        }
        .contact-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .contact-box:hover {
            transform: translateY(-5px);
        }
        .contact-box h4 {
            margin-bottom: 20px;
            color: #28a745;
        }
        label {
            font-weight: bold;
        }
        footer {
            background-color: #28a745;
            color: white;
            padding: 15px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>Contact Us</h1>
    <p>We are here to help you!</p>
</div>

<!-- Contact Form Section -->
<div class="container contact-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="contact-box">
                <h4>Get in Touch</h4>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="name">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter message subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Write your message here" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Send Message</button>
                </form>
            </div>

            <div class="contact-box text-center">
                <h4>Our Contact Info</h4>
                <p>📧 Email: support@internshipsystem.com</p>
                <p>📞 Phone: +91 1234567890</p>
                <p>🏢 Address: 123, Internship Street, Your City, India</p>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date("Y"); ?> Internship Management System. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
