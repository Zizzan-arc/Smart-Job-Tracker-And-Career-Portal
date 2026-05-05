<?php
session_start();
include '../Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit();
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$githubUrl = trim($_POST['github_url'] ?? '');
$experienceYears = intval($_POST['experience_years'] ?? 0);

$errors = [];
if (empty($firstName)) $errors[] = 'First name is required';
if (empty($lastName)) $errors[] = 'Last name is required';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
if ($experienceYears < 0) $errors[] = 'Experience years must be non-negative';

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    header('Location: register.html');
    exit();
}

$stmt = $conn->prepare("SELECT UserID FROM User WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $_SESSION['register_errors'] = ['Email already registered'];
    $stmt->close();
    header('Location: register.html');
    exit();
}
$stmt->close();

$roleColumnResult = $conn->query("SHOW COLUMNS FROM User LIKE 'Role'");
$hasRoleColumn = $roleColumnResult && $roleColumnResult->num_rows > 0;

if ($hasRoleColumn) {
    $userStmt = $conn->prepare("INSERT INTO User (First_Name, Last_Name, Email, Password, Role) VALUES (?, ?, ?, ?, 'Applicant')");
    $userStmt->bind_param("ssss", $firstName, $lastName, $email, $password);
} else {
    $userStmt = $conn->prepare("INSERT INTO User (First_Name, Last_Name, Email, Password) VALUES (?, ?, ?, ?)");
    $userStmt->bind_param("ssss", $firstName, $lastName, $email, $password);
}

if (!$userStmt->execute()) {
    $_SESSION['register_errors'] = ['Registration failed: ' . $userStmt->error];
    $userStmt->close();
    header('Location: register.html');
    exit();
}
$userId = $conn->insert_id;
$userStmt->close();

$applicantStmt = $conn->prepare("INSERT INTO Applicant (UserID, GitHub_URL, Experience_Years, Referral_Points) VALUES (?, ?, ?, 0)");
$applicantStmt->bind_param("isi", $userId, $githubUrl, $experienceYears);
if (!$applicantStmt->execute()) {
    $conn->query("DELETE FROM User WHERE UserID = $userId");
    $_SESSION['register_errors'] = ['Registration failed: ' . $applicantStmt->error];
    $applicantStmt->close();
    header('Location: register.html');
    exit();
}
$applicantStmt->close();

$_SESSION['user_id'] = $userId;
$_SESSION['current_user_id'] = $userId;

header('Location: ../onboarding/skills.php');
exit();
?>