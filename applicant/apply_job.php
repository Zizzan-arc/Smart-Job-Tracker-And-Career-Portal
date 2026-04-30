<?php
session_start();
include '../Database.php';

// Get user ID
$userId = $_SESSION['current_user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo "Invalid request";
    exit();
}

$jobId = intval($_POST['job_id'] ?? 0);

if ($jobId <= 0) {
    http_response_code(400);
    echo "Invalid job ID";
    exit();
}

// Check if already applied
$checkResult = $conn->query("SELECT 1 FROM appliesto WHERE UserID = $userId AND Job_ID = $jobId LIMIT 1");
if ($checkResult && $checkResult->num_rows > 0) {
    http_response_code(400);
    echo "Already applied";
    exit();
}

// Check mandatory skills
$mandatoryResult = $conn->query(
    "SELECT COUNT(*) AS total_mandatory
     FROM Requires_Skill
     WHERE Job_ID = $jobId AND Is_Mandatory = 1"
);
$matchedMandatoryResult = $conn->query(
    "SELECT COUNT(*) AS matched_mandatory
     FROM Requires_Skill rs
     JOIN Has_Skill hs ON rs.Skill_ID = hs.Skill_ID
     WHERE rs.Job_ID = $jobId AND rs.Is_Mandatory = 1 AND hs.UserID = $userId"
);

$matchedCountResult = $conn->query(
    "SELECT COUNT(*) AS matched_count
     FROM Requires_Skill rs
     JOIN Has_Skill hs ON rs.Skill_ID = hs.Skill_ID
     WHERE rs.Job_ID = $jobId AND hs.UserID = $userId"
);

$total_mandatory = $mandatoryResult ? intval($mandatoryResult->fetch_assoc()['total_mandatory']) : 0;
$matchedMandatory = $matchedMandatoryResult ? intval($matchedMandatoryResult->fetch_assoc()['matched_mandatory']) : 0;
$matchedCount = $matchedCountResult ? intval($matchedCountResult->fetch_assoc()['matched_count']) : 0;

if ($matchedCount === 0) {
    http_response_code(400);
    echo "Cannot apply: no matching skills for this job.";
    exit();
}

if ($total_mandatory > 0 && $matchedMandatory < $total_mandatory) {
    http_response_code(400);
    echo "Cannot apply: mandatory skills are missing.";
    exit();
}

// Insert application
$sql = "INSERT INTO appliesto (UserID, Job_ID, Application_date, Status) VALUES ($userId, $jobId, NOW(), 'Pending')";
if ($conn->query($sql) === TRUE) {
    http_response_code(200);
    echo "Success";
} else {
    http_response_code(500);
    echo "Error: " . $conn->error;
}
?>
