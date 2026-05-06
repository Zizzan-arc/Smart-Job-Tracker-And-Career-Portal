<?php
require_once __DIR__ . '/auth.php';
include '../Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_job.php');
    exit;
}

// 1. Get the data from the form
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

// 2. Validation
if (!$jobTitle || !$baseSalary || !$workModel || !$employmentType || !$deadline || !$companyId) {
    echo "<script>
    alert('Please fill all required job fields.'); 
    window.location.href = 'create_job.php';
    </script>";
    exit;
}

// 3. Prepare skill IDs
$requiredSkillIds = [];
foreach ($requiredSkills as $skillId) {
    $requiredSkillIds[] = intval($skillId);
}

$niceSkillIds = [];
foreach ($niceSkills as $skillId) {
    $niceSkillIds[] = intval($skillId);
}

// 4. Handle "Other Skill" (if the admin typed a new one)
if ($otherSkill !== '') {
    $otherSanitized = $conn->real_escape_string($otherSkill);
    $existingSkill = $conn->query("SELECT Skill_ID FROM Skill WHERE LOWER(Skill_Name) = LOWER('$otherSanitized') LIMIT 1");
    if ($existingSkill && $existingSkill->num_rows > 0) {
        $otherSkillId = intval($existingSkill->fetch_assoc()['Skill_ID']);
    } else {
        $conn->query("INSERT INTO Skill (Skill_Name) VALUES ('$otherSanitized')");
        $otherSkillId = $conn->insert_id ? intval($conn->insert_id) : 0;
    }
    if ($otherSkillId > 0) {
        $requiredSkillIds[] = $otherSkillId;
    }
}

$requiredSkillIds = array_filter(array_unique($requiredSkillIds));
$niceSkillIds = array_filter(array_unique($niceSkillIds));

// 5. Clean strings for SQL
$safeTitle = $conn->real_escape_string($jobTitle);
$safeSalary = $conn->real_escape_string($baseSalary);
$safeWork = $conn->real_escape_string($workModel);
$safeType = $conn->real_escape_string($employmentType);
$safeDeadline = $conn->real_escape_string($deadline);

// 6. Insert the Job (Using correct capitalized table names)
$sql = "INSERT INTO JobPost (Job_Title, Base_Salary, Work_Model, Employment_Type, Deadline, Company_ID) 
        VALUES ('$safeTitle', '$safeSalary', '$safeWork', '$safeType', '$safeDeadline', $companyId)";

if ($conn->query($sql) === TRUE) {
    $jobId = $conn->insert_id;

    // Link Required Skills
    foreach ($requiredSkillIds as $skillId) {
        if ($skillId > 0) {
            $conn->query("INSERT IGNORE INTO Requires_Skill (Job_ID, Skill_ID, Is_Mandatory) VALUES ($jobId, $skillId, 1)");
        }
    }

    // Link Nice-to-have Skills
    foreach ($niceSkillIds as $skillId) {
        if ($skillId > 0 && !in_array($skillId, $requiredSkillIds, true)) {
            $conn->query("INSERT IGNORE INTO Requires_Skill (Job_ID, Skill_ID, Is_Mandatory) VALUES ($jobId, $skillId, 0)");
        }
    }

    // Link Categories
    foreach ($categories as $categoryId) {
        if (intval($categoryId) > 0) {
            $conn->query("INSERT IGNORE INTO Job_Category (Job_ID, Category_ID) VALUES ($jobId, $categoryId)");
        }
    }

    echo "<script>alert('Job created successfully.'); window.location.href = 'jobs.php';</script>";
    exit;
}

echo "<script>alert('Could not create job: " . $conn->error . "'); window.location.href = 'create_job.php';</script>";
exit;
?>