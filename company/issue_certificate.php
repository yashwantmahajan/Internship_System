<?php
session_start();
require '../db/db.php';

if (!isset($_SESSION['company_id'])) {
    header("Location: ../auth/company_login.php");
    exit;
}

$company_id = intval($_SESSION['company_id']);

if (!isset($_GET['app_id']) || !isset($_GET['internship_id'])) {
    header("Location: company_dashboard.php");
    exit;
}

$app_id       = intval($_GET['app_id']);
$internship_id = intval($_GET['internship_id']);

// Fetch full details for certificate
$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT u.name AS student_name, i.title AS internship_title,
           i.start_date, i.end_date,
           cu.name AS company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN internships i ON a.internship_id = i.id
    JOIN companies c ON i.company_id = c.id
    JOIN users cu ON c.user_id = cu.id
    WHERE a.id = $app_id AND i.company_id = $company_id
"));

if (!$data) {
    die("❌ Invalid application or access denied.");
}

// Generate certificate HTML file
$cert_dir = '../uploads/certificates/';
if (!is_dir($cert_dir)) mkdir($cert_dir, 0755, true);
$cert_filename = 'cert_' . $app_id . '_' . time() . '.html';
$cert_path_relative = 'uploads/certificates/' . $cert_filename;
$cert_path_full = $cert_dir . $cert_filename;

$student_name     = htmlspecialchars($data['student_name']);
$internship_title = htmlspecialchars($data['internship_title']);
$company_name     = htmlspecialchars($data['company_name']);
$issue_date       = date('F d, Y');
$start_date       = $data['start_date'] ? date('F d, Y', strtotime($data['start_date'])) : 'N/A';
$end_date         = $data['end_date']   ? date('F d, Y', strtotime($data['end_date']))   : 'N/A';

$cert_html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Internship Certificate — $student_name</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#f0f4ff;display:flex;justify-content:center;align-items:center;min-height:100vh;font-family:'Outfit',sans-serif;}
.cert{width:960px;min-height:680px;background:#fff;position:relative;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);border:2px solid #4F46E5;}
.cert-inner{padding:60px 70px;position:relative;z-index:2;}
.cert-bg{position:absolute;inset:0;opacity:0.04;z-index:1;background:radial-gradient(circle at 20% 50%,#4F46E5 0%,transparent 50%),radial-gradient(circle at 80% 50%,#10B981 0%,transparent 50%);}
.cert-top{border-top:8px solid #4F46E5;border-bottom:3px solid #4F46E5;padding:20px 0;text-align:center;margin-bottom:40px;}
.cert-logo{font-size:1.2rem;color:#4F46E5;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;}
.cert-heading{font-family:'Playfair Display',serif;font-size:2.8rem;font-weight:700;color:#1E293B;margin-bottom:4px;}
.cert-sub{font-size:1rem;color:#64748B;letter-spacing:3px;text-transform:uppercase;}
.cert-body{text-align:center;}
.cert-body p{font-size:1.1rem;color:#475569;margin-bottom:0.75rem;line-height:1.7;}
.cert-name{font-family:'Playfair Display',serif;font-size:2.2rem;color:#4F46E5;font-weight:700;margin:1rem 0;}
.cert-role{font-size:1.3rem;font-weight:600;color:#1E293B;margin:0.5rem 0;}
.cert-company{font-size:1.1rem;color:#10B981;font-weight:600;}
.cert-dates{display:inline-flex;gap:3rem;background:#F8FAFC;border-radius:12px;padding:1rem 2rem;margin:1.5rem 0;border:1px solid #E2E8F0;}
.cert-dates div{text-align:center;}
.cert-dates label{display:block;font-size:0.8rem;color:#94A3B8;text-transform:uppercase;letter-spacing:1px;}
.cert-dates span{font-weight:600;color:#1E293B;}
.cert-footer{display:flex;justify-content:space-between;align-items:flex-end;margin-top:50px;padding-top:30px;border-top:1px solid #E2E8F0;}
.cert-sign{text-align:center;}
.sign-line{width:180px;border-bottom:2px solid #CBD5E1;margin:0 auto 8px;}
.sign-label{font-size:0.85rem;color:#64748B;}
.cert-seal{width:80px;height:80px;border:3px solid #4F46E5;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;}
@media print{body{background:white;} .no-print{display:none;}}
</style>
</head>
<body>
<div class="cert">
    <div class="cert-bg"></div>
    <div class="cert-inner">
        <div class="cert-top">
            <div class="cert-logo">🎓 InternPortal</div>
            <div class="cert-heading">Certificate of Internship</div>
            <div class="cert-sub">This is to certify that</div>
        </div>
        <div class="cert-body">
            <div class="cert-name">$student_name</div>
            <p>has successfully completed the internship program as</p>
            <div class="cert-role">$internship_title</div>
            <p>at</p>
            <div class="cert-company">$company_name</div>
            <div class="cert-dates">
                <div><label>Start Date</label><span>$start_date</span></div>
                <div><label>End Date</label><span>$end_date</span></div>
                <div><label>Issued On</label><span>$issue_date</span></div>
            </div>
            <p style="font-size:0.95rem; color:#94A3B8;">
                We commend their dedication, professionalism, and valuable contributions during this program.
            </p>
        </div>
        <div class="cert-footer">
            <div class="cert-sign">
                <div class="sign-line"></div>
                <div class="sign-label">Authorized Signatory<br><strong>$company_name</strong></div>
            </div>
            <div class="cert-seal">🏆</div>
            <div class="cert-sign">
                <div class="sign-line"></div>
                <div class="sign-label">Platform Director<br><strong>InternPortal</strong></div>
            </div>
        </div>
    </div>
</div>
<div class="no-print" style="text-align:center; margin-top:1rem;">
    <button onclick="window.print()" style="padding:10px 24px;background:#4F46E5;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:1rem;font-weight:600;">🖨️ Print Certificate</button>
</div>
</body>
</html>
HTML;

file_put_contents($cert_path_full, $cert_html);

// Update DB: mark certificate issued and store path
mysqli_query($conn, "UPDATE applications SET certificate_issued='yes', certificate_path='$cert_path_relative' WHERE id=$app_id");

header("Location: view_applicants.php?internship_id=$internship_id&cert=issued");
exit;
?>
