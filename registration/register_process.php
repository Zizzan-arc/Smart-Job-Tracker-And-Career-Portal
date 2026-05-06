<?php
session_start();
include '../Database.php';

// 1. Get the data from the form
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$selectedRole = $_POST['role'] ?? 'Applicant';
$experienceYears = intval($_POST['experience_years'] ?? 0);
$githubUrl = $_POST['github_url'] ?? '';

// 2. Simple Validation
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

// 3. Clean the data (Security)
$safeEmail = $conn->real_escape_string($email);
$safeFName = $conn->real_escape_string($firstName);
$safeLName = $conn->real_escape_string($lastName);
$safePass = $conn->real_escape_string($password);
$safeGithub = $conn->real_escape_string($githubUrl);

// 4. Check if the Email is already taken
$checkQuery = "SELECT * FROM User WHERE Email = '$safeEmail'";
$result = $conn->query($checkQuery);

if ($result && $result->num_rows > 0) {
    $_SESSION['register_errors'] = ['This email is already registered.'];
    header('Location: register.html');
    exit();
}

// 5. Insert into User table
$userSql = "INSERT INTO User (First_Name, Last_Name, Email, Password, Role) 
            VALUES ('$safeFName', '$safeLName', '$safeEmail', '$safePass', '$selectedRole')";

if ($conn->query($userSql)) {
    $userId = $conn->insert_id;
    
    // AUTO-LOGIN: Set the session for the new user
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $selectedRole;

    // 6. If they are an Applicant, add to Applicant table and go to SKILLS
    if ($selectedRole === 'Applicant') {
        $applicantSql = "INSERT INTO Applicant (UserID, GitHub_URL, Experience_Years, Referral_Points) 
                         VALUES ($userId, '$safeGithub', $experienceYears, 0)";
        $conn->query($applicantSql);
        
        // Redirect to Onboarding Skills page
        header('Location: ../onboarding/skills.php');
        exit();
    } elseif ($selectedRole === 'Admin') {
        // Also save to the admin table
        $conn->query("INSERT INTO admin (UserID) VALUES ($userId)");
        
        // If Admin, go to Admin Dashboard
        header('Location: ../admin/index.php');
        exit();
    }
} else {
    $_SESSION['register_errors'] = ['Database error: ' . $conn->error];
    header('Location: register.html');
}
?>