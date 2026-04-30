<?php
session_start();
include '../Database.php';

header('Content-Type: application/json');

$userId = intval($_POST['user_id'] ?? 0);
$jobId = intval($_POST['job_id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowedStatuses = ['Pending', 'Interviewed', 'Rejected'];

if ($userId <= 0 || $jobId <= 0 || !in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

$statusEscaped = $conn->real_escape_string($status);
$sql = "UPDATE appliesto SET Status = '$statusEscaped' WHERE UserID = $userId AND Job_ID = $jobId";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>