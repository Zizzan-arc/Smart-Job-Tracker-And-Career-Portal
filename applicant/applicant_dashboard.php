<?php
session_start();
include '../Database.php';

// 1. AUTHENTICATION & SESSION
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}


$applicantResult = $conn->query("SELECT u.First_Name, u.Last_Name, a.Experience_Years, a.Referral_Points FROM User u JOIN Applicant a ON u.UserID = a.UserID WHERE u.UserID = $userId");
$applicant = $applicantResult->fetch_assoc();

// fetching skills
$skillsResult = $conn->query("SELECT s.Skill_ID, s.Skill_name FROM Has_Skill h JOIN Skill s ON h.Skill_ID = s.Skill_ID WHERE h.UserID = $userId");
$applicantSkills = [];
while ($skill = $skillsResult->fetch_assoc()) {
    $applicantSkills[] = $skill;
}
//   (Already Applied / Already Saved)
$appliedJobs = [];
$savedJobIds = [];
$appliedResult = $conn->query("SELECT Job_ID FROM appliesto WHERE UserID = $userId");
while ($row = $appliedResult->fetch_assoc()) {
    $appliedJobs[] = $row['Job_ID'];
}
$savedResult = $conn->query("SELECT Job_ID FROM Wishlist WHERE UserID = $userId");
while ($row = $savedResult->fetch_assoc()) {
    $savedJobIds[] = $row['Job_ID'];
}

// Recommended jobs section
$recommendedJobs = [];
if (!empty($applicantSkills)) {
    $recResult = $conn->query(
        "SELECT j.Job_ID, j.Job_title, c.Company_name, j.Base_salary, j.Deadline, j.Employment_Type,
                TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left,
                (TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48) AS expiring_soon,
                COUNT(DISTINCT hs.Skill_ID) AS match_count,
                SUM(CASE WHEN rs.Is_Mandatory = 1 THEN 1 ELSE 0 END) AS total_mandatory,
                SUM(CASE WHEN rs.Is_Mandatory = 1 AND hs.Skill_ID IS NOT NULL THEN 1 ELSE 0 END) AS matched_mandatory
         FROM JobPost j
         JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
         LEFT JOIN Has_Skill hs ON rs.Skill_ID = hs.Skill_ID AND hs.UserID = $userId
         LEFT JOIN Company c ON j.Company_ID = c.Company_ID
         GROUP BY j.Job_ID, j.Job_title, c.Company_name, j.Base_salary, j.Deadline, j.Employment_Type
        -- at least one skill 
         HAVING COUNT(DISTINCT hs.Skill_ID) > 0
         ORDER BY match_count DESC, j.Deadline ASC
         LIMIT 5"
    );
    while ($job = $recResult->fetch_assoc()) {
        $recommendedJobs[] = $job;
    }
}

// SAVED JOBS(Skill Gap Analysis)
$savedJobsQuery = "
    SELECT j.Job_ID, j.Job_title, c.Company_name, j.Base_salary, j.Deadline, j.Employment_Type, j.Work_Model,
           TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left,
           (TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48) AS expiring_soon,
           COUNT(DISTINCT CASE WHEN rs.Skill_ID IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS match_count,
           COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 THEN rs.Skill_ID END) AS total_mandatory,
           COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS matched_mandatory,
           COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID NOT IN (SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId) THEN rs.Skill_ID END) AS missing_mandatory
    FROM Wishlist w
    JOIN JobPost j ON w.Job_ID = j.Job_ID
    LEFT JOIN Company c ON j.Company_ID = c.Company_ID
    LEFT JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
    WHERE w.UserID = $userId
    GROUP BY j.Job_ID, j.Job_title, c.Company_name, j.Base_salary, j.Deadline, j.Employment_Type, j.Work_Model
    ORDER BY w.Date_Saved DESC
";
$savedJobsResult = $conn->query($savedJobsQuery);
$savedJobs = [];
while ($job = $savedJobsResult->fetch_assoc()) {
    $savedJobs[] = $job;
}

//  TRENDING JOBS
$trendingResult = $conn->query(
    "SELECT j.Job_ID,
            j.Job_title,
            c.Company_name,
            j.Base_salary,
            j.Deadline,
            COUNT(a.UserID) as app_count,
            TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left,
            (TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48) AS expiring_soon
     FROM appliesto a
     JOIN JobPost j ON a.Job_ID = j.Job_ID
     LEFT JOIN Company c ON j.Company_ID = c.Company_ID
     GROUP BY j.Job_ID, j.Job_title, c.Company_name, j.Base_salary, j.Deadline
     ORDER BY app_count DESC
     LIMIT 2"
);
$trendingJobs = [];
while ($job = $trendingResult->fetch_assoc()) {
    $trendingJobs[] = $job;
}

// 8. FETCH DEADLINE REMINDERS (Saved jobs expiring within 48 hours)
$deadlineReminders = [];
$reminderQuery = "
    SELECT j.Job_ID, j.Job_title, c.Company_name, j.Deadline,
           TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left
    FROM Wishlist w
    JOIN JobPost j ON w.Job_ID = j.Job_ID
    LEFT JOIN Company c ON j.Company_ID = c.Company_ID
    WHERE w.UserID = $userId 
    AND TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48
    ORDER BY j.Deadline ASC
";
$reminderResult = $conn->query($reminderQuery);
while ($reminder = $reminderResult->fetch_assoc()) {
    $deadlineReminders[] = $reminder;
}

// 9. FETCH APPLICATION STATUS
$statusResult = $conn->query(
    "SELECT a.Job_ID, a.Status, a.Application_date, j.Job_title, c.Company_name, j.Deadline,
            TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) AS hours_left,
            (TIMESTAMPDIFF(HOUR, NOW(), j.Deadline) BETWEEN 0 AND 48) AS expiring_soon
     FROM appliesto a
     JOIN JobPost j ON a.Job_ID = j.Job_ID
     LEFT JOIN Company c ON j.Company_ID = c.Company_ID
     WHERE a.UserID = $userId
     ORDER BY a.Application_date DESC"
);
$applications = [];
while ($app = $statusResult->fetch_assoc()) {
    $applications[] = $app;
}
?>

<!-- the html part -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($applicant['First_Name']); ?></h1>
                <p class="text-slate-600">Your job search dashboard</p>
                <p class="text-sm text-slate-500 mt-2">Referral points: <strong><?php echo intval($applicant['Referral_Points'] ?? 0); ?></strong></p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/browse_jobs.php" class="btn btn-primary">Browse Jobs</a>
                <a href="/Jobportal/applicant/saved_jobs.php" class="btn btn-secondary">Saved Jobs</a>
                <a href="/Jobportal/applicant/cv_workspace.php" class="btn btn-secondary">CV Workspace</a>
                <a href="/Jobportal/applicant/applied_jobs.php" class="btn btn-outline btn-accent">Applied Jobs</a>
                <a href="/Jobportal/logout.php" class="btn btn-outline btn-error">Logout</a>
            </div>
        </div>

        <!-- Deadline Reminders Alert -->
        <?php if (!empty($deadlineReminders)): ?>
        <div class="alert alert-warning shadow-lg mb-8 bg-orange-50 border-l-4 border-orange-500">
            <div class="flex items-start gap-4">
                <span class="text-2xl">⏰</span>
                <div class="flex-1">
                    <h3 class="font-semibold text-orange-900">Application Deadlines Coming Up!</h3>
                    <p class="text-sm text-orange-800 mt-1">You have <?php echo count($deadlineReminders); ?> saved job(s) with deadline(s) in the next 48 hours:</p>
                    <div class="mt-3 space-y-2">
                        <?php foreach ($deadlineReminders as $reminder): ?>
                            <div class="flex justify-between items-center bg-white p-2 rounded border border-orange-200">
                                <div>
                                    <p class="font-medium text-orange-900"><?php echo htmlspecialchars($reminder['Job_title']); ?></p>
                                    <p class="text-xs text-orange-700"><?php echo htmlspecialchars($reminder['Company_name'] ?: 'Company'); ?> • Deadline: <?php echo htmlspecialchars($reminder['Deadline']); ?></p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="badge badge-error"><?php echo intval($reminder['hours_left']); ?> hrs left</span>
                                    <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $reminder['Job_ID']; ?>" class="btn btn-xs btn-primary">Apply</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Your Skills -->
        <?php if (!empty($applicantSkills)): ?>
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Your Skills</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($applicantSkills as $skill): ?>
                    <span class="badge badge-primary"><?php echo htmlspecialchars($skill['Skill_name']); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid gap-8">
            <!-- Recommended Jobs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Recommended For You</h2>
                <?php if (!empty($recommendedJobs)): ?>
                    <div class="space-y-3">
                        <?php foreach ($recommendedJobs as $job): ?>
                            <div class="border rounded-lg p-4 hover:bg-slate-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($job['Job_title']); ?></h3>
                                        <p class="text-slate-600"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                        <div class="flex gap-4 text-sm text-slate-600 mt-2">
                                            <span>💰 <?php echo number_format($job['Base_salary'], 0); ?></span>
                                            <span>📅 <?php echo htmlspecialchars($job['Deadline']); ?> <span class="text-xs <?php echo $job['expiring_soon'] ? 'text-error font-bold' : 'text-slate-400'; ?>">(<?php echo $job['hours_left']; ?>h left)</span></span>
                                            <span><?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                            <?php if ($job['expiring_soon']): ?>
                                                <span class="badge badge-error">Expiring Soon</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 items-end">
                                        <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                                        
                                        <?php if (in_array($job['Job_ID'], $appliedJobs)): ?>
                                            <span class="badge badge-success">Applied</span>
                                        <?php else: ?>
                                            <?php 
                                                $canApply = ($job['match_count'] > 0 && ($job['total_mandatory'] == 0 || $job['matched_mandatory'] == $job['total_mandatory']));
                                            ?>
                                            <?php if ($canApply): ?>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="applyJob(<?php echo $job['Job_ID']; ?>)">Apply</button>
                                            <?php else: ?>
                                                <?php if (in_array($job['Job_ID'], $savedJobIds)): ?>
                                                    <button type="button" class="btn btn-sm btn-outline btn-error" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Remove</button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline" onclick="saveJob(<?php echo $job['Job_ID']; ?>)">Save</button>
                                                <?php endif; ?>
                                                <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-sm btn-secondary">Skill Gap</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($applicantSkills)): ?>
                        <p class="text-slate-600">No recommended jobs match your current skills yet. Browse jobs to explore more opportunities.</p>
                    <?php else: ?>
                        <p class="text-slate-600">No recommended jobs yet. Complete your skills profile to see recommendations.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Saved Jobs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Saved Jobs</h2>
                <?php if (!empty($savedJobs)): ?>
                    <div class="space-y-3">
                        <?php foreach ($savedJobs as $job): ?>
                            <div class="border rounded-lg p-4 hover:bg-slate-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($job['Job_title']); ?></h3>
                                        <p class="text-slate-600"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                        <div class="flex gap-4 text-sm text-slate-600 mt-2">
                                            <span>💰 <?php echo number_format($job['Base_salary'], 0); ?></span>
                                            <span>📅 <?php echo htmlspecialchars($job['Deadline']); ?> <span class="text-xs <?php echo $job['expiring_soon'] ? 'text-error font-bold' : 'text-slate-400'; ?>">(<?php echo $job['hours_left']; ?>h left)</span></span>
                                            <span><?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                            <?php if ($job['expiring_soon']): ?>
                                                <span class="badge badge-error">Expiring Soon</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <?php if ($job['missing_mandatory'] > 0): ?>
                                                <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $job['Job_ID']; ?>" class="text-orange-600 hover:text-orange-800">
                                                    Missing <?php echo $job['missing_mandatory']; ?> mandatory skill(s) for this job
                                                </a>
                                            <?php else: ?>
                                                <span class="text-green-600">You have all mandatory skills for this job.</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 items-end">
                                        <button type="button" 
                                                class="btn btn-sm <?php echo $job['missing_mandatory'] > 0 ? 'btn-outline' : 'btn-primary'; ?>" 
                                                <?php echo $job['missing_mandatory'] > 0 ? 'disabled title="Missing mandatory skills"' : 'onclick="applyJob(' . $job['Job_ID'] . ')"'; ?>>
                                            Apply Now
                                        </button>
                                        <button type="button" class="btn btn-outline btn-error btn-sm" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-600">No saved jobs yet. Browse jobs and save ones you're interested in!</p>
                <?php endif; ?>
            </div>

            <!-- Trending Jobs -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Trending Jobs</h2>
                <?php if (!empty($trendingJobs)): ?>
                    <div class="space-y-3">
                        <?php foreach ($trendingJobs as $job): ?>
                            <div class="border rounded-lg p-4 hover:bg-slate-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($job['Job_title']); ?></h3>
                                        <p class="text-slate-600"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                        <div class="flex gap-4 text-sm text-slate-600 mt-2">
                                            <span>💰 <?php echo number_format($job['Base_salary'], 0); ?></span>
                                            <span>📅 <?php echo htmlspecialchars($job['Deadline']); ?> 
                                                <span class="text-xs <?php echo $job['expiring_soon'] ? 'text-error font-bold' : 'text-slate-400'; ?>">
                                                    (<?php echo $job['hours_left']; ?>h left)
                                                </span>
                                            </span>
                                            <span>🔥 <?php echo $job['app_count']; ?> applications</span>
                                            <?php if ($job['expiring_soon']): ?>
                                                <span class="badge badge-error">Expiring Soon</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="/Jobportal/applicant/browse_jobs.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-600">No Trending Jobs yet.</p>
                <?php endif; ?>
            </div>

            <!-- Application Status -->
            <?php if (!empty($applications)): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Your Application Status</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Applied On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['Job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($app['Company_name'] ?: 'Company'); ?></td>
                                    <td>
                                        <?php 
                                            $statusClass = $app['Status'] == 'Accepted' ? 'badge-success' : ($app['Status'] == 'Rejected' ? 'badge-error' : 'badge-warning'); 
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($app['Status']); ?>
                                        </span>
                                        <?php if ($app['expiring_soon']): ?>
                                            <span class="badge badge-error ml-2">Expiring Soon (<?php echo $app['hours_left']; ?>h)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['Application_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
