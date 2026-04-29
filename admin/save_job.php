<?php
include '../Database.php';

// basically ensuring that the form is submitted then it landed here.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_job.php');
    exit;
}

$jobTitle       = $_POST['job_title'];
$baseSalary     = $_POST['base_salary'];
$workModel      = $_POST['work_model'];
$employmentType = $_POST['employment_type'];
$deadline       = $_POST['deadline'];
$companyId      = $_POST['company_id'];

//  empty list [] if nothing was found
$skills = $_POST['required_skills'];

if (empty($skills)) {
    $skills = [];
}

if (!$jobTitle || !$baseSalary || !$workModel || !$employmentType || !$deadline || !$companyId) {
    echo "<script>
    alert('Please fill all required job fields.'); 
    window.location.href = 'create_job.php';
    </script>";
    exit;
}

$sql = "INSERT INTO jobpost (Job_title, Base_salary, Work_Model, Employment_Type, Deadline, Company_ID) VALUES ('$jobTitle', '$baseSalary', '$workModel', '$employmentType', '$deadline', $companyId)";

// to check it is successfull query or 
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