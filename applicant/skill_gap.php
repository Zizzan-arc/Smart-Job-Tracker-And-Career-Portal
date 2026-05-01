<?php
session_start();
include '../Database.php';

// Get current logged-in user ID
$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;

if (!$userId) {
    header('Location: /Jobportal/index.html');
    exit();
}

$jobId = intval($_GET['job_id'] ?? 0);
if ($jobId <= 0) {
    header('Location: /Jobportal/applicant/browse_jobs.php');
    exit();
}

function getMissingSkills($conn, $userId, $jobId) {
    $missingSkills = [];

    $query = "SELECT s.Skill_ID, s.Skill_name
              FROM Requires_Skill rs
              JOIN Skill s ON rs.Skill_ID = s.Skill_ID
              WHERE rs.Job_ID = $jobId
              AND rs.Skill_ID NOT IN (
                  SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId
              )";

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $missingSkills[] = $row;
    }

    return $missingSkills;
}

function getJobTitle($conn, $jobId) {
    $query = "SELECT j.Job_title, c.Company_name
              FROM JobPost j
              LEFT JOIN Company c ON j.Company_ID = c.Company_ID
              WHERE j.Job_ID = $jobId";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc() : null;
}

$job = getJobTitle($conn, $jobId);
if (!$job) {
    header('Location: /Jobportal/applicant/browse_jobs.php');
    exit();
}

$allowed = false;
$checkSaved = $conn->query("SELECT 1 FROM Wishlist WHERE UserID = $userId AND Job_ID = $jobId LIMIT 1");
$checkApplied = $conn->query("SELECT 1 FROM appliesto WHERE UserID = $userId AND Job_ID = $jobId LIMIT 1");
if (($checkSaved && $checkSaved->num_rows > 0) || ($checkApplied && $checkApplied->num_rows > 0)) {
    $allowed = true;
}

if (!$allowed) {
    header('Location: /Jobportal/applicant/applicant_dashboard.php');
    exit();
}

$missingSkills = getMissingSkills($conn, $userId, $jobId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Gap Analyzer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Skill Gap Analyzer</h1>
                <p class="text-slate-600">See which skills you still need for this job.</p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/browse_jobs.php" class="btn btn-outline">← Browse Jobs</a>
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-primary">Dashboard</a>
                <a href="/Jobportal/logout.php" class="btn btn-outline btn-error">Logout</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-2"><?php echo htmlspecialchars($job['Job_title']); ?></h2>
            <p class="text-slate-600">Company: <?php echo htmlspecialchars($job['Company_name'] ?: 'Unknown'); ?></p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-4">Missing Skills</h3>
            <?php if (!empty($missingSkills)): ?>
                <ul class="space-y-3">
                    <?php foreach ($missingSkills as $skill): ?>
                        <li class="border rounded-lg p-4 bg-slate-50">
                            <?php echo htmlspecialchars($skill['Skill_name']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-slate-600">Good news! You already have all the skills required for this job.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
