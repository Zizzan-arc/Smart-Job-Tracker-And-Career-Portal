<?php
include '../Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_job.php');
    exit;
}

$jobTitle = $conn->real_escape_string($_POST['job_title'] ?? '');
$baseSalary = $conn->real_escape_string($_POST['base_salary'] ?? '0');
$workModel = $conn->real_escape_string($_POST['work_model'] ?? '');
$employmentType = $conn->real_escape_string($_POST['employment_type'] ?? '');
$deadline = $conn->real_escape_string($_POST['deadline'] ?? '');
$companyId = intval($_POST['company_id'] ?? 0);
$skills = $_POST['required_skills'] ?? [];

if (!$jobTitle || !$baseSalary || !$workModel || !$employmentType || !$deadline || !$companyId) {
    echo "<script>alert('Please fill all required job fields.'); window.location.href = 'create_job.php';</script>";
    exit;
}

$sql = "INSERT INTO jobpost (Job_title, Base_salary, Work_Model, Employment_Type, Deadline, Company_ID) VALUES ('$jobTitle', '$baseSalary', '$workModel', '$employmentType', '$deadline', $companyId)";

if ($conn->query($sql) === TRUE) {
    $jobId = $conn->insert_id;
    foreach ($skills as $skillId) {
        $skillId = intval($skillId);
        if ($skillId > 0) {
            $conn->query("INSERT INTO requires_skill (Job_ID, Skill_ID, Is_madatory) VALUES ($jobId, $skillId, 0)");
        }
    }
    echo "<script>alert('Job created successfully.'); window.location.href = 'jobs.php';</script>";
    exit;
}

echo "<script>alert('Could not create job.'); window.location.href = 'create_job.php';</script>";
exit;
?>