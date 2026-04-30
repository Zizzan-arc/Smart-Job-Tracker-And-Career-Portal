<?php

session_start();

include 'Database.php';

// email and password submitted from the login form (index.html) 
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Query the user table to find a matching account
$sql = "SELECT * FROM user WHERE Email = '$email' AND Password = '$password'";
$result = $conn->query($sql);


if ($result->num_rows == 1) {

    // converts the object into an Array
    $user = $result->fetch_assoc();

    // Store the user's ID and Role in the session
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['role'] = $user['Role'];

    // Redirect based on the user's role by using header function of php
    if ($user['Role'] == 'Applicant') {
        header("Location: /Jobportal/applicant/applicant_dashboard.php");
        exit();
    } elseif ($user['Role'] == 'Admin') {
        header("Location: /Jobportal/admin/index.php");
        exit();
    }

} else {
    // Login failed and redirect
    echo "<script>
        alert('Login Failed');
        window.location.href = '/Jobportal/index.html';
    </script>";
}
?>
