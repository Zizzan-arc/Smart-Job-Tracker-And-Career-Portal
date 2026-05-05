<?php
session_start();
include '../Database.php';

// Check if user is from registration
if (!isset($_SESSION['user_id'])) {
    header('Location: /Jobportal/registration/register.html');
    exit;
}

// $_SESSION = [
//   "user_id" => 5,
//   "email" => "test@gmail.com",
//   "role" => "admin"
// ];

$userId = $_SESSION['user_id'];

// Get selected skills and any other skill from the form submission (HTML part).
// basically receiving the input from the user.

$selectedSkills = $_POST['selected_skills'] ?? [];
$otherSkill = trim($_POST['other_skill'] ?? '');

if (empty($selectedSkills) && $otherSkill === '') {
    echo "<script>
        alert('Error: Please select at least one skill or enter a new skill.');
        window.location.href = '/Jobportal/onboarding/skills.php';
    </script>";
    exit;
}


// array_map is used to convert the string into into because HTML form sends String values
// these two things are basically cleaning the data and making sure there are no duplicates
$selectedSkills = array_filter(array_map('intval', $selectedSkills));
$selectedSkills = array_unique($selectedSkills);

// checking if the (typed skill) is in the database or not
if ($otherSkill !== '') {
    $otherSanitized = $conn->real_escape_string($otherSkill);
    $skillResult = $conn->query("SELECT Skill_ID FROM Skill WHERE LOWER(Skill_Name) = LOWER('$otherSanitized') LIMIT 1");
    if ($skillResult && $skillResult->num_rows > 0) {
        $row = $skillResult->fetch_assoc();
        $otherSkillId = $row['Skill_ID'];
    } 
    else {
        $conn->query("INSERT INTO Skill (Skill_Name, Trend_Score, BaseValue) VALUES ('$otherSanitized', 1, 1)");
        $otherSkillId = $conn->insert_id;
    }

    if ($otherSkillId > 0) {
        // adding into the list of skills 
        $selectedSkills[] = $otherSkillId;
    }
}

// Has_skill part.
$insertedCount = 0;
foreach ($selectedSkills as $skillId) {
    if ($skillId <= 0) {
        continue;
    }
    // this part is basically the applicant has selected which skills they have,checkbox 
    $conn->query("INSERT IGNORE INTO Has_Skill (UserID, Skill_ID) VALUES ($userId, $skillId)");
    if ($conn->affected_rows > 0) {
        $insertedCount++;
    }
}
// just to check if skills were inserted or not.
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
