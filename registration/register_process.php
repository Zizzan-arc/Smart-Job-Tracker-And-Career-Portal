<?php
session_start();
include '../Database.php';

// 1. Get the data from the form
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$selectedRole = $_POST['role'] ?? 'Applicant';
$experienceYears = intval($_POST['experienceYears'] ?? 0);
$githubUrl = $_POST['githubUrl'] ?? '';

// 2. Simple Validation (Checks if everything is filled out)
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    $_SESSION['register_errors'] = ['All fields are required.'];
    header('Location: register.html');
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['register_errors'] = ['Passwords do not match.'];
    header('Location: register.html');
    exit();
}

// 3. Clean the data (Beginner safety)
$safeEmail = $conn->real_escape_string($email);
$safeFName = $conn->real_escape_string($firstName);
$safeLName = $conn->real_escape_string($lastName);
$safePass = $conn->real_escape_string($password);
$safeGithub = $conn->real_escape_string($githubUrl);

// 4. Check if the Email is already taken
$checkQuery = "SELECT * FROM User WHERE Email = '$safeEmail'";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    $_SESSION['register_errors'] = ['This email is already registered.'];
    header('Location: register.html');
    exit();
}

// 5. Insert into User table
$userSql = "INSERT INTO User (First_Name, Last_Name, Email, Password, Role) 
            VALUES ('$safeFName', '$safeLName', '$safeEmail', '$safePass', '$selectedRole')";

if ($conn->query($userSql)) {
    $userId = $conn->insert_id;

    // 6. If they are an Applicant, also add to the Applicant table
    if ($selectedRole === 'Applicant') {
        $applicantSql = "INSERT INTO Applicant (UserID, GitHub_URL, Experience_Years, Referral_Points) 
                         VALUES ($userId, '$safeGithub', $experienceYears, 0)";
        $conn->query($applicantSql);
    }

    // Success! Redirect to login
    header('Location: ../index.html?registered=success');
} else {
    $_SESSION['register_errors'] = ['Database error: ' . $conn->error];
    header('Location: register.html');
}
?>