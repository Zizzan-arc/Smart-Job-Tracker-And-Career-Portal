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
$action = $_POST['action'] ?? '';

if ($jobId <= 0 || !in_array($action, ['save', 'unsave'])) {
    http_response_code(400);
    echo "Invalid job ID or action";
    exit();
}

if ($action === 'save') {
    // Check if already saved
    $checkResult = $conn->query("SELECT Wishlist_ID FROM Wishlist WHERE UserID = $userId AND Job_ID = $jobId");
    if ($checkResult && $checkResult->num_rows > 0) {
        http_response_code(400);
        echo "Already saved";
        exit();
    }

    // Save the job
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