<?php
session_start();
include '../Database.php';

// Check if user is from registration
if (!isset($_SESSION['current_user_id'])) {
    header('Location: ../registration/register.html');
    exit;
}

$userId = $_SESSION['current_user_id'];

// Get selected skills from form
$selectedSkills = $_POST['selected_skills'] ?? [];

if (empty($selectedSkills)) {
    echo "<script>
        alert('Error: Please select at least one skill.');
        window.location.href = 'skills.php';
    </script>";
    $conn->close();
    exit;
}

// Insert each selected skill into Has_Skill table
$insertedCount = 0;
foreach ($selectedSkills as $skillId) {
    $skillId = intval($skillId); // Sanitize
    $sql = "INSERT INTO Has_Skill (UserID, Skill_ID) VALUES ($userId, $skillId)";
    if ($conn->query($sql) === TRUE) {
        $insertedCount++;
    }
}

if ($insertedCount > 0) {
    echo "<script>
        alert('Skills saved successfully!');
        window.location.href = '../index.html';
    </script>";
} else {
    echo "<script>
        alert('Error: Could not save skills. Please try again.');
        window.location.href = 'skills.php';
    </script>";
}

$conn->close();
?>
