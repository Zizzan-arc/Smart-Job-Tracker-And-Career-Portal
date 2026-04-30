<?php
session_start();
include '../Database.php';

// Check if user is from registration
if (!isset($_SESSION['current_user_id'])) {
    header('Location: /Jobportal/registration/register.html');
    exit;
}

$userId = $_SESSION['current_user_id'];

// Get selected skills and any other skill from form
$selectedSkills = $_POST['selected_skills'] ?? [];
$otherSkill = trim($_POST['other_skill'] ?? '');

if (empty($selectedSkills) && $otherSkill === '') {
    echo "<script>
        alert('Error: Please select at least one skill or enter a new skill.');
        window.location.href = '/Jobportal/onboarding/skills.php';
    </script>";
    exit;
}

// Normalize selected skills to unique integers
$selectedSkills = array_filter(array_map('intval', $selectedSkills));
$selectedSkills = array_unique($selectedSkills);

// If other skill is provided, use existing skill or insert a new one
if ($otherSkill !== '') {
    $otherSanitized = $conn->real_escape_string($otherSkill);
    $skillResult = $conn->query("SELECT Skill_ID FROM Skill WHERE LOWER(Skill_Name) = LOWER('$otherSanitized') LIMIT 1");
    if ($skillResult && $skillResult->num_rows > 0) {
        $otherSkillId = intval($skillResult->fetch_assoc()['Skill_ID']);
    } else {
        $conn->query("INSERT INTO Skill (Skill_Name, Trend_Score, BaseValue) VALUES ('$otherSanitized', 1, 1)");
        $otherSkillId = $conn->insert_id;
    }

    if ($otherSkillId > 0) {
        $selectedSkills[] = $otherSkillId;
    }
}

$insertedCount = 0;
foreach ($selectedSkills as $skillId) {
    if ($skillId <= 0) {
        continue;
    }
    $conn->query("INSERT IGNORE INTO Has_Skill (UserID, Skill_ID) VALUES ($userId, $skillId)");
    if ($conn->affected_rows > 0) {
        $insertedCount++;
    }
}

if ($insertedCount > 0) {
    echo "<script>
        alert('Skills saved successfully!');
        window.location.href = '/Jobportal/applicant/applicant_dashboard.php';
    </script>";
} else {
    echo "<script>
        alert('Error: Could not save skills. Please try again.');
        window.location.href = '/Jobportal/onboarding/skills.php';
    </script>";
}
?>
