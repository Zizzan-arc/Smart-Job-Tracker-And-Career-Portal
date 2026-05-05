<?php
session_start();
include 'Database.php';

// 1. Get the data from the login form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// 2. Simple check: Are the fields empty?
if ($email === '' || $password === '') {
    echo "<script>
        alert('Login Failed: Email and password are required.');
        window.location.href = '/Jobportal/index.html';
    </script>";
    exit();
}

// 3. Clean the email input
$safeEmail = $conn->real_escape_string($email);

// 4. Find the user in the database
$sql = "SELECT * FROM User WHERE Email = '$safeEmail' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 5. Compare the password (direct comparison)
    if ($password === $user['Password']) {
        
        // 6. Set the session variables
        $_SESSION['user_id'] = $user['UserID'];
        $role = ucfirst(strtolower(trim($user['Role'] ?: 'Applicant')));
        $_SESSION['role'] = $role;

        // 7. Redirect based on role
        if ($role === 'Applicant') {
            header("Location: /Jobportal/applicant/applicant_dashboard.php");
            exit();
        } elseif ($role === 'Admin') {
            header("Location: /Jobportal/admin/index.php");
            exit();
        }
    }
}

// 8. If something went wrong, show an alert
echo "<script>
    alert('Login Failed: Invalid email or password.');
    window.location.href = '/Jobportal/index.html';
</script>";
exit();
?>
