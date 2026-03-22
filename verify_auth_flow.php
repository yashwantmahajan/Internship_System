<?php
// verify_auth_flow.php
require 'db/db.php';

function test_scenario($name, $callback) {
    echo "Testing: $name ... ";
    try {
        $callback();
        echo "SUCCESS\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

function curl_post($url, $data, &$cookie_jar) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    return ['body' => $response, 'info' => $info];
}

$base_url = "http://localhost:8000";
$cookie_file = tempnam(sys_get_temp_dir(), 'cookie');

echo "========================================\n";
echo "Starting Authentication Flow Verification\n";
echo "Target: $base_url\n";
echo "========================================\n\n";

// 1. Test Student Registration
test_scenario("Student Registration", function() use ($base_url, $cookie_file, $conn) {
    $email = "test_student_" . time() . "@example.com";
    $password = "password123";
    
    $data = [
        'name' => 'Tech Student',
        'email' => $email,
        'password' => $password,
        'phone' => '9876543210',
        'college' => 'IMIT Cuttack',
        'degree' => 'MCA'
    ];
    
    $res = curl_post("$base_url/auth/register_student.php", $data, $cookie_file);
    
    if (strpos($res['body'], 'Student registered successfully') === false) {
        // Check DB to be sure (maybe redirect happened?)
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows == 0) {
            throw new Exception("Registration failed, verification text not found and DB row missing.");
        }
    }
});

// 2. Test Student Login
test_scenario("Student Login", function() use ($base_url, $cookie_file, $conn) {
    // Determine the email we just created (or use a fixed one if we want determinsm, but dynamic is better)
    // Actually, let's just create a FRESH one for login to be atomic
    $email = "login_student_" . time() . "@example.com";
    $password = "password123";
    
    // Manually insert for speed/reliability of THIS test
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('Login User', '$email', '$pass_hash', 'student')");
    $uid = $conn->insert_id;
    $conn->query("INSERT INTO students (user_id, phone, college, degree) VALUES ($uid, '1234567890', 'Test College', 'BTech')");
    
    $data = [
        'email' => $email,
        'password' => $password
    ];
    
    $res = curl_post("$base_url/auth/student_login.php", $data, $cookie_file);
    
    // Check if we were redirected to student_dashboard.php
    $final_url = $res['info']['url'];
    if (strpos($final_url, 'student_dashboard.php') === false) {
        throw new Exception("Login failed, did not redirect to dashboard. Landed on: $final_url");
    }
});

// 3. Test Admin Login
test_scenario("Admin Login", function() use ($base_url, $cookie_file) {
    $email = "admin@test.com";
    $password = "admin123";
    
    $data = [
        'email' => $email,
        'password' => $password
    ];
    
    $res = curl_post("$base_url/auth/admin_login.php", $data, $cookie_file);
    
    $final_url = $res['info']['url'];
    if (strpos($final_url, 'admin_dashboard.php') === false) {
        throw new Exception("Admin login failed. Landed on: $final_url");
    }
});

echo "\nVerification Completed.\n";
unlink($cookie_file);
?>
