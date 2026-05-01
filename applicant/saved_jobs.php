<?php
session_start();
include '../Database.php';

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$savedQuery = "SELECT j.Job_ID, j.Job_Title, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name,
                      TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left,
                      (TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48) AS expiring_soon,
                      COUNT(DISTINCT CASE WHEN rs.Skill_ID IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS match_count,
                      COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 THEN rs.Skill_ID END) AS total_mandatory,
                      COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS matched_mandatory
               FROM Wishlist w
               JOIN JobPost j ON w.Job_ID = j.Job_ID
               LEFT JOIN Company c ON j.Company_ID = c.Company_ID
               LEFT JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
               WHERE w.UserID = $userId
               GROUP BY j.Job_ID, j.Job_Title, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name
               HAVING match_count > 0
               ORDER BY w.Date_Saved DESC";

$savedResult = $conn->query($savedQuery);
$savedJobs = [];
while ($row = $savedResult->fetch_assoc()) {
    $savedJobs[] = $row;
}

$appliedResult = $conn->query("SELECT Job_ID FROM appliesto WHERE UserID = $userId");
$appliedJobs = [];
while ($row = $appliedResult->fetch_assoc()) {
    $appliedJobs[] = $row['Job_ID'];
}

function getMissingSkills($conn, $userId, $jobId) {
    $missingSkills = [];
    $skillQuery = "SELECT s.Skill_Name
                    FROM Requires_Skill rs
                    JOIN Skill s ON rs.Skill_ID = s.Skill_ID
                    WHERE rs.Job_ID = $jobId
                      AND rs.Skill_ID NOT IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId)";
    $result = $conn->query($skillQuery);
    while ($row = $result->fetch_assoc()) {
        $missingSkills[] = $row['Skill_Name'];
    }
    return $missingSkills;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Saved Jobs</h1>
                <p class="text-slate-600">Target jobs for future learning and skill growth.</p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/applicant/browse_jobs.php" class="btn btn-secondary">Browse Jobs</a>
                <a href="/Jobportal/applicant/applied_jobs.php" class="btn btn-primary">Applied Jobs</a>
                <a href="/Jobportal/logout.php" class="btn btn-error btn-outline">Logout</a>
            </div>
        </div>

        <!-- Job details in the saved_jobs  section -->
             <!-- if it is not empty the saved job part-->
        <?php if (!empty($savedJobs)): ?>
            <div class="space-y-4">
                <?php foreach ($savedJobs as $job): ?>
                    <?php $missingSkills = getMissingSkills($conn, $userId, $job['Job_ID']); ?>
                    <div class="border rounded-xl p-6 bg-white shadow-sm <?php echo $job['expiring_soon'] ? 'border-red-400 bg-red-50' : ''; ?>">
                        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-start">
                            <div class="flex-1">
                                <h2 class="text-2xl font-semibold"><?php echo htmlspecialchars($job['Job_Title']); ?></h2>
                                <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-600">
                                    <span>💰 <?php echo number_format($job['Base_Salary'], 0); ?></span>
                                    <span>🏢 <?php echo htmlspecialchars($job['Work_Model']); ?></span>
                                    <span>⏰ <?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                    <span>📅 Deadline: <?php echo htmlspecialchars($job['Deadline']); ?></span>
                                </div>
                                <?php if ($job['expiring_soon']): ?>
                                    <div class="mt-3 text-sm text-red-700 font-semibold">Deadline within 48 hours</div>
                                <?php endif; ?>
                                <div class="mt-4">
                                    <h3 class="font-semibold">Missing Skills</h3>
                                    <?php if (!empty($missingSkills)): ?>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <?php foreach ($missingSkills as $skillName): ?>
                                                <span class="badge badge-outline"><?php echo htmlspecialchars($skillName); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-green-700 mt-2">You already have all required skills for this role.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 lg:items-end">
                                <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary">View Details</a>
                                <?php if (in_array($job['Job_ID'], $appliedJobs)): ?>
                                    <span class="badge badge-success">Applied</span>
                                <?php elseif ($canApply): ?>
                                    <button type="button" class="btn btn-primary" onclick="applyJob(<?php echo $job['Job_ID']; ?>)">Apply</button>
                                <?php else: ?>
                                    <span class="badge badge-warning">Mandatory skills missing</span>
                                    <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary">Skill Gap</a>
                                    <button type="button" class="btn btn-outline btn-error" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Remove</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm p-6 text-slate-600 text-center">
                You have not saved any jobs yet. Use Browse Jobs to add target roles for future learning.
            </div>
        <?php endif; ?>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
