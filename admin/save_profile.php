<?php
require_once __DIR__ . '/auth.php';
include '../Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$adminId = intval($_POST['admin_id'] ?? 0);
$email = $conn->real_escape_string($_POST['email'] ?? '');
$password = $conn->real_escape_string($_POST['password'] ?? '');

if (!$adminId || !$email || !$password) {
    echo "<script>alert('Please provide all profile details.'); window.location.href = 'profile.php';</script>";
    exit;
}

// Prevent updating to an email that already belongs to another user
$checkEmailSql = "SELECT UserID FROM User WHERE Email = '$email' AND UserID <> $adminId";
$emailResult = $conn->query($checkEmailSql);
if ($emailResult && $emailResult->num_rows > 0) {
    echo "<script>alert('This email is already used by another account. Please use a different email.'); window.location.href = 'profile.php';</script>";
    exit;
}

$sql = "UPDATE User SET Email = '$email', Password = '$password' WHERE UserID = $adminId AND Role = 'admin'";
if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Profile updated successfully.'); window.location.href = 'profile.php';</script>";
    exit;
}

echo "<script>alert('Could not update profile. Please try again.'); window.location.href = 'profile.php';</script>";
exit;
?>