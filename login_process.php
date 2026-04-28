<?php

session_start();

include 'Database.php';

// email and password submitted from the login form (index.html) 
//$_POST is an global associative array that receives data from html FORM
$email = $_POST['email'];
$password = $_POST['password'];

// Query the User table to find a user with matching email and password (PHP and MySQl bridge)
$sql = "SELECT * FROM User WHERE Email = '$email' AND Password = '$password'";
$result = $conn->query($sql);


if ($result->num_rows == 1) {

    // converts the object into an Array
    $user = $result->fetch_assoc();

    // Store the user's ID and Role in the session
    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['role'] = $user['Role'];

    // Redirect based on the user's role by using header function of php
    if ($user['Role'] == 'Applicant') {
        header("Location: applicant_dashboard.php");
        exit();
    } elseif ($user['Role'] == 'Admin') {
        header("Location: admin_dashboard.php");
        exit();
    }

} else {
    // Login failed and redirect
    echo "<script>
        alert('Login Failed');
        window.location.href = 'index.html';
    </script>";
}
?>
