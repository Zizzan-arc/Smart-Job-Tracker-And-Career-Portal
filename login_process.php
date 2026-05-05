<?php

session_start();
include 'Database.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<script>
        alert('Login Failed: Email and password are required.');
        window.location.href = '/Jobportal/index.html';
    </script>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM User WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $isPasswordValid = ($password === $user['Password']);

    if ($isPasswordValid) {
        $role = trim($user['Role'] ?? 'Applicant');
        if ($role === '') {
            $role = 'Applicant';
        }
        $role = ucfirst(strtolower($role));

        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role'] = $role;

        if ($role === 'Applicant') {
            header("Location: /Jobportal/applicant/applicant_dashboard.php");
            exit();
        } elseif ($role === 'Admin') {
            header("Location: /Jobportal/admin/index.php");
            exit();
        }
    }
}

echo "<script>
    alert('Login Failed');
    window.location.href = '/Jobportal/index.html';
</script>";
exit();
?>
