<?php
session_start();
include '../Database.php';

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$appliedQuery = "
    SELECT a.Job_ID, j.Job_Title, c.Company_name, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline,
           a.Status, a.Application_date,
           COUNT(DISTINCT rs.Skill_ID) AS total_skills,
           COUNT(DISTINCT CASE WHEN rs.Skill_ID NOT IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS missing_skills,
           SUM(CASE WHEN rs.Is_Mandatory = 1 THEN 1 ELSE 0 END) AS total_mandatory,
           SUM(CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID NOT IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN 1 ELSE 0 END) AS missing_mandatory
    FROM appliesto a
    JOIN JobPost j ON a.Job_ID = j.Job_ID
    LEFT JOIN Company c ON j.Company_ID = c.Company_ID
    LEFT JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
    WHERE a.UserID = $userId
    GROUP BY a.Job_ID, j.Job_Title, c.Company_name, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, a.Status, a.Application_date
    ORDER BY a.Application_date DESC
";

$result = $conn->query($appliedQuery);
$appliedJobs = [];
while ($row = $result->fetch_assoc()) {
    $appliedJobs[] = $row;
}
?>

<!-- the HTML part -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applied Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Applied Jobs</h1>
                <p class="text-slate-600">Review all jobs you have applied to, including rejected ones.</p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/applicant/browse_jobs.php" class="btn btn-secondary">Browse Jobs</a>
                <a href="/Jobportal/applicant/saved_jobs.php" class="btn btn-secondary">Saved Jobs</a>
                <a href="/Jobportal/logout.php" class="btn btn-error btn-outline">Logout</a>
            </div>
        </div>

        <?php if (!empty($appliedJobs)): ?>
            <div class="space-y-4">
                <?php foreach ($appliedJobs as $job): ?>
                    <div class="border rounded-xl p-6 bg-white shadow-sm hover:bg-slate-50">
                        <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-start">
                            <div class="flex-1">
                                <h2 class="text-2xl font-semibold"><?php echo htmlspecialchars($job['Job_Title']); ?></h2>
                                <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                                    <span>💰 <?php echo number_format($job['Base_Salary'], 0); ?></span>
                                    <span>🏢 <?php echo htmlspecialchars($job['Work_Model']); ?></span>
                                    <span>⏰ <?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                    <span>📅 Deadline: <?php echo htmlspecialchars($job['Deadline']); ?></span>
                                </div>
                                <div class="mt-3">
                                    <span class="badge <?php echo $job['Status'] === 'Accepted' ? 'badge-success' : ($job['Status'] === 'Rejected' ? 'badge-error' : 'badge-warning'); ?>">
                                        <?php echo htmlspecialchars($job['Status']); ?>
                                    </span>
                                    <span class="text-slate-500 ml-2">Applied on <?php echo htmlspecialchars($job['Application_date']); ?></span>
                                </div>
                                <?php if ($job['missing_mandatory'] > 0): ?>
                                    <div class="mt-4 text-orange-700 font-semibold">
                                        Missing <?php echo intval($job['missing_mandatory']); ?> mandatory skill(s)
                                    </div>
                                <?php elseif ($job['missing_skills'] > 0): ?>
                                    <div class="mt-4 text-orange-700 font-semibold">
                                        Missing <?php echo intval($job['missing_skills']); ?> preferred skill(s
                                        )
                                    </div>
                                <?php else: ?>
                                    <div class="mt-4 text-green-700 font-semibold">You meet all required skills.</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col gap-3 lg:items-end">
                                <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm p-6 text-slate-600 text-center">
                You have not applied to any jobs yet. Browse jobs to start applying.
            </div>
        <?php endif; ?>
    </div>
    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
