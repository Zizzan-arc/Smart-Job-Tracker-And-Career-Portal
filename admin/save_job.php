<?php
include '../Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_job.php');
    exit;
}

$jobTitle       = trim($_POST['job_title'] ?? '');
$baseSalary     = $_POST['base_salary'] ?? '';
$workModel      = trim($_POST['work_model'] ?? '');
$employmentType = trim($_POST['employment_type'] ?? '');
$deadline       = trim($_POST['deadline'] ?? '');
$companyId      = intval($_POST['company_id'] ?? 0);
$categories     = $_POST['categories'] ?? [];
$requiredSkills = $_POST['required_skills'] ?? [];
$niceSkills     = $_POST['nice_skills'] ?? [];
$otherSkill     = trim($_POST['other_skill'] ?? '');

if (!$jobTitle || !$baseSalary || !$workModel || !$employmentType || !$deadline || !$companyId) {
    echo "<script>
    alert('Please fill all required job fields.'); 
    window.location.href = 'create_job.php';
    </script>";
    exit;
}

$requiredSkillIds = [];
foreach ($requiredSkills as $skillId) {
    $requiredSkillIds[] = intval($skillId);
}

$niceSkillIds = [];
foreach ($niceSkills as $skillId) {
    $niceSkillIds[] = intval($skillId);
}

if ($otherSkill !== '') {
    $otherSanitized = $conn->real_escape_string($otherSkill);
    $existingSkill = $conn->query("SELECT Skill_ID FROM skill WHERE LOWER(Skill_Name) = LOWER('$otherSanitized') LIMIT 1");
    if ($existingSkill && $existingSkill->num_rows > 0) {
        $otherSkillId = intval($existingSkill->fetch_assoc()['Skill_ID']);
    } else {
        $conn->query("INSERT INTO skill (Skill_Name, Trend_Score, BaseValue) VALUES ('$otherSanitized', 1, 1)");
        $otherSkillId = $conn->insert_id ? intval($conn->insert_id) : 0;
    }
    if ($otherSkillId > 0) {
        $requiredSkillIds[] = $otherSkillId;
    }
}

$requiredSkillIds = array_filter(array_unique($requiredSkillIds));
$niceSkillIds = array_filter(array_unique($niceSkillIds));

$sql = "INSERT INTO jobpost (Job_Title, Base_Salary, Work_Model, Employment_Type, Deadline, Company_ID) VALUES ('" . $conn->real_escape_string($jobTitle) . "', '" . $conn->real_escape_string($baseSalary) . "', '" . $conn->real_escape_string($workModel) . "', '" . $conn->real_escape_string($employmentType) . "', '" . $conn->real_escape_string($deadline) . "', $companyId)";

if ($conn->query($sql) === TRUE) {
    $jobId = $conn->insert_id;

    foreach ($requiredSkillIds as $skillId) {
        $skillId = intval($skillId);
        if ($skillId > 0) {
            $conn->query("INSERT IGNORE INTO requires_skill (Job_ID, Skill_ID, Is_Mandatory) VALUES ($jobId, $skillId, 1)");
        }
    }

    foreach ($niceSkillIds as $skillId) {
        $skillId = intval($skillId);
        if ($skillId > 0 && !in_array($skillId, $requiredSkillIds, true)) {
            $conn->query("INSERT IGNORE INTO requires_skill (Job_ID, Skill_ID, Is_Mandatory) VALUES ($jobId, $skillId, 0)");
        }
    }

    foreach ($categories as $categoryId) {
        $categoryId = intval($categoryId);
        if ($categoryId > 0) {
            $conn->query("INSERT IGNORE INTO job_category (Job_ID, Category_ID) VALUES ($jobId, $categoryId)");
        }
    }

    echo "<script>alert('Job created successfully.'); window.location.href = 'jobs.php';</script>";
    exit;
}

echo "<script>alert('Could not create job.'); window.location.href = 'create_job.php';</script>";
exit;
?>