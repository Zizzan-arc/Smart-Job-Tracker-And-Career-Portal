<?php
// submit_review.php - Handle company review submission
session_start();
require_once '../Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? $_SESSION['UserID'] ?? 0;
$companyId = intval($_POST['company_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$reviewText = trim($_POST['review_text'] ?? '');
$isAnonymous = intval($_POST['is_anonymous'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($companyId <= 0 || $rating < 1 || $rating > 5 || empty($reviewText)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Basic sanitization
$reviewText = htmlspecialchars($reviewText, ENT_QUOTES, 'UTF-8');

try {
    // Check if user already reviewed this company (composite primary key check)
    $checkStmt = $conn->prepare("SELECT UserID FROM leave_review WHERE UserID = ? AND Company_ID = ?");
    $checkStmt->bind_param("ii", $userId, $companyId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing review
        $stmt = $conn->prepare("UPDATE leave_review SET Rating = ?, Feedback = ?, Is_Anonymous = ?, Date_Submitted = NOW() WHERE UserID = ? AND Company_ID = ?");
        $stmt->bind_param("isiii", $rating, $reviewText, $isAnonymous, $userId, $companyId);
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO leave_review (UserID, Company_ID, Rating, Feedback, Is_Anonymous, Date_Submitted) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisii", $userId, $companyId, $rating, $reviewText, $isAnonymous);
    }
    $checkStmt->close();

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save review: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
