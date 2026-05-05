<?php
// submit_review.php - Handle company review submission
session_start();
include '../Database.php';

// 1. Get the data from the form
$userId = $_SESSION['user_id'] ?? 0;
$companyId = intval($_POST['company_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$reviewText = trim($_POST['review_text'] ?? '');
$isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;

// 2. Security Check: Is the user logged in?
if ($userId <= 0) {
    echo "User not logged in";
    exit;
}

// 3. Validation: Is the data correct?
if ($companyId <= 0 || $rating < 1 || $rating > 5 || empty($reviewText)) {
    echo "Please fill all fields correctly.";
    exit;
}

// 4. Sanitize the text (prevents breaking the database query)
$safeReview = $conn->real_escape_string($reviewText);

// 5. Check if the user has already reviewed this company
$checkQuery = "SELECT * FROM leave_review WHERE UserID = $userId AND Company_ID = $companyId";
$checkResult = $conn->query($checkQuery);

if ($checkResult->num_rows > 0) {
    // If they already reviewed, UPDATE the old one
    $sql = "UPDATE leave_review 
            SET Rating = $rating, Feedback = '$safeReview', Is_Anonymous = $isAnonymous, Date_Submitted = NOW() 
            WHERE UserID = $userId AND Company_ID = $companyId";
} else {
    // If it's a new review, INSERT it
    $sql = "INSERT INTO leave_review (UserID, Company_ID, Rating, Feedback, Is_Anonymous, Date_Submitted) 
            VALUES ($userId, $companyId, $rating, '$safeReview', $isAnonymous, NOW())";
}

// 6. Execute the query and send result back to JavaScript
if ($conn->query($sql)) {
    echo "Success";
} else {
    echo "Database error: " . $conn->error;
}
?>
