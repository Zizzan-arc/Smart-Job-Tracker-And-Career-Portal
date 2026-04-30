<?php
session_start();
include '../Database.php';

$userId = $_SESSION['current_user_id'] ?? $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$jobId = intval($_GET['job_id'] ?? 0);
if ($jobId <= 0) {
    header('Location: job_listing.php');
    exit();
}

$jobResult = $conn->query(
    "SELECT j.Job_ID, j.Job_Title, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, j.Company_ID,
            c.Company_Name
     FROM JobPost j
     LEFT JOIN Company c ON j.Company_ID = c.Company_ID
     WHERE j.Job_ID = $jobId"
);

if (!$jobResult || $jobResult->num_rows === 0) {
    header('Location: job_listing.php');
    exit();
}

$job = $jobResult->fetch_assoc();

$skillResult = $conn->query(
    "SELECT s.Skill_name, rs.Is_Mandatory
     FROM Requires_Skill rs
     JOIN Skill s ON rs.Skill_ID = s.Skill_ID
     WHERE rs.Job_ID = $jobId"
);
$requiredSkills = [];
while ($skill = $skillResult->fetch_assoc()) {
    $requiredSkills[] = $skill;
}

$appliedCheck = $conn->query("SELECT UserID FROM appliesto WHERE UserID = $userId AND Job_ID = $jobId");
$hasApplied = $appliedCheck && $appliedCheck->num_rows > 0;

$savedCheck = $conn->query("SELECT Wishlist_ID FROM Wishlist WHERE UserID = $userId AND Job_ID = $jobId");
$hasSaved = $savedCheck && $savedCheck->num_rows > 0;

$skillGapResult = $conn->query(
    "SELECT COUNT(*) AS missing_count
     FROM Requires_Skill rs
     WHERE rs.Job_ID = $jobId
       AND rs.Skill_ID NOT IN (
           SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId
       )"
);
$skillGap = $skillGapResult ? intval($skillGapResult->fetch_assoc()['missing_count']) : 0;

$matchCountResult = $conn->query(
    "SELECT COUNT(*) AS match_count
     FROM Requires_Skill rs
     WHERE rs.Job_ID = $jobId
       AND rs.Skill_ID IN (
           SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId
       )"
);
$matchCount = $matchCountResult ? intval($matchCountResult->fetch_assoc()['match_count']) : 0;

$mandatoryResult = $conn->query(
    "SELECT COUNT(*) AS total_mandatory,
            SUM(CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID IN (
                    SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId
                ) THEN 1 ELSE 0 END) AS matched_mandatory
     FROM Requires_Skill rs
     WHERE rs.Job_ID = $jobId"
);
$mandatoryData = $mandatoryResult ? $mandatoryResult->fetch_assoc() : ['total_mandatory' => 0, 'matched_mandatory' => 0];
$totalMandatory = intval($mandatoryData['total_mandatory']);
$matchedMandatory = intval($mandatoryData['matched_mandatory']);
$canApply = ($matchCount > 0 && ($totalMandatory === 0 || $matchedMandatory === $totalMandatory));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['Job_Title']); ?> | Job Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="job_listing.php" class="text-blue-600 hover:text-blue-800">← Back to Jobs</a>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/logout.php" class="btn btn-outline btn-error">Logout</a>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-start">
                        <div>
                            <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($job['Job_Title']); ?></h1>
                            <p class="text-slate-600 mt-2"><?php echo htmlspecialchars($job['Company_Name'] ?: 'Company'); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-slate-500">Deadline</div>
                            <div class="font-semibold"><?php echo htmlspecialchars($job['Deadline']); ?></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 text-sm text-slate-700">
                        <div class="rounded-lg border p-4">
                            <div class="font-semibold">Salary</div>
                            <div>₹ <?php echo number_format($job['Base_Salary'], 0); ?></div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="font-semibold">Work Model</div>
                            <div><?php echo htmlspecialchars($job['Work_Model']); ?></div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="font-semibold">Employment Type</div>
                            <div><?php echo htmlspecialchars($job['Employment_Type']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Required Skills</h2>
                    <?php if (!empty($requiredSkills)): ?>
                        <div class="grid gap-3">
                            <?php foreach ($requiredSkills as $skill): ?>
                                <div class="flex items-center gap-3">
                                    <span class="badge <?php echo $skill['Is_Mandatory'] ? 'badge-error' : 'badge-warning'; ?>">
                                        <?php echo $skill['Is_Mandatory'] ? 'Required' : 'Preferred'; ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($skill['Skill_name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-600">No specific skills listed for this job.</p>
                    <?php endif; ?>

                    <div class="mt-6 p-4 rounded-lg bg-slate-50">
                        <?php if ($skillGap === 0): ?>
                            <p class="text-green-700">You already have all required skills for this job.</p>
                        <?php else: ?>
                            <p class="text-orange-700">You are missing <?php echo $skillGap; ?> skill(s) for this job.</p>
                            <?php if ($hasSaved || $hasApplied): ?>
                                <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $jobId; ?>" class="btn btn-sm btn-primary mt-3 inline-block">View Skill Gap</a>
                            <?php else: ?>
                                <p class="mt-3 text-slate-600">Save this job to view the full skill gap analysis.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <?php if ($hasApplied): ?>
                        <div class="alert alert-success">
                            <span>You have already applied to this job.</span>
                        </div>
                    <?php else: ?>
                        <?php if ($canApply): ?>
                            <button type="button" class="btn btn-primary w-full" onclick="applyJob(<?php echo $jobId; ?>)">Apply Now</button>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <span>You are missing mandatory skills for this job. Save it and use Skill Gap Analysis.</span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($hasSaved): ?>
                        <button type="button" class="btn btn-outline btn-error w-full" onclick="unsaveJob(<?php echo $jobId; ?>)">Remove from Saved</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline w-full" onclick="saveJob(<?php echo $jobId; ?>)">Save Job</button>
                    <?php endif; ?>
                    <?php if ($hasSaved || $hasApplied): ?>
                        <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $jobId; ?>" class="btn btn-secondary w-full">Skill Gap Analysis</a>
                    <?php else: ?>
                        <p class="text-slate-600 mt-3 text-center">Skill gap available after saving or applying to this job.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
