<?php
session_start();
include '../Database.php';

//  user ID
$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;

// these checks are for mild security purposes. 
if (!$userId) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}
// if the user uses GET method that is by typing the url directly , it will throw a error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo "Invalid request";
    exit();
}

$jobId = intval($_POST['job_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($jobId <= 0 || !in_array($action, ['save', 'unsave'])) {
    http_response_code(400);
    echo "Invalid job ID or action";
    exit();
}

if ($action === 'save') {
    $checkResult = $conn->query("SELECT Wishlist_ID FROM Wishlist WHERE UserID = $userId AND Job_ID = $jobId");
    if ($checkResult && $checkResult->num_rows > 0) {
        http_response_code(400);
        echo "Already saved";
        exit();
    }

    $mandatoryResult = $conn->query(
        "SELECT COUNT(*) AS total_mandatory
         FROM Requires_Skill
         WHERE Job_ID = $jobId AND Is_Mandatory = 1"
    );
    $matchedResult = $conn->query(
        "SELECT COUNT(*) AS matched_mandatory
         FROM Requires_Skill rs
         JOIN Has_Skill hs ON rs.Skill_ID = hs.Skill_ID
         WHERE rs.Job_ID = $jobId AND rs.Is_Mandatory = 1 AND hs.UserID = $userId"
    );

    $totalMandatory = $mandatoryResult ? intval($mandatoryResult->fetch_assoc()['total_mandatory']) : 0;
    $matchedMandatory = $matchedResult ? intval($matchedResult->fetch_assoc()['matched_mandatory']) : 0;

    if ($totalMandatory === $matchedMandatory) {
        http_response_code(400);
        echo "Cannot save this job because your mandatory skills are already met for this job.";
        exit();
    }

    $sql = "INSERT INTO Wishlist (UserID, Job_ID, Date_Saved) VALUES ($userId, $jobId, NOW())";
    if ($conn->query($sql) === TRUE) {
        http_response_code(200);
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . $conn->error;
    }
} else if ($action === 'unsave') {
    // Remove from saved jobs
    $sql = "DELETE FROM Wishlist WHERE UserID = $userId AND Job_ID = $jobId";
    if ($conn->query($sql) === TRUE) {
        http_response_code(200);
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error: " . $conn->error;
    }
}
?>