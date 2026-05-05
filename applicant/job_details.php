<?php
session_start();
include '../Database.php';

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? $_SESSION['UserID'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$jobId = intval($_GET['job_id'] ?? 0);
if ($jobId <= 0) {
    header('Location: browse_jobs.php');
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
    header('Location: browse_jobs.php');
    exit();
}

$job = $jobResult->fetch_assoc();
$referrerId = intval($_GET['referrer'] ?? 0);

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

// Fetch company reviews
$companyId = $job['Company_ID'];
$reviewsResult = $conn->query("SELECT r.UserID, r.Rating, r.Feedback, r.Date_Submitted, r.Is_Anonymous, u.First_Name, u.Last_Name 
                               FROM leave_review r 
                               JOIN user u ON r.UserID = u.UserID 
                               WHERE r.Company_ID = $companyId 
                               ORDER BY r.Date_Submitted DESC");
$reviews = [];
$averageRating = 0;
$totalReviews = 0;
while ($review = $reviewsResult->fetch_assoc()) {
    $reviews[] = $review;
    $averageRating += $review['Rating'];
    $totalReviews++;
}
if ($totalReviews > 0) {
    $averageRating = round($averageRating / $totalReviews, 1);
}
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
                <a href="/Jobportal/applicant/browse_jobs.php" class="text-blue-600 hover:text-blue-800">← Back to Browse Jobs</a>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/applicant/cv_workspace.php" class="btn btn-secondary">CV Workspace</a>
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
                    <?php if ($referrerId <= 0 || $referrerId === $userId): ?>
                        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h3 class="font-semibold">Referral Link</h3>
                            <p class="text-sm text-slate-600 mb-3">Share this link with another applicant. If they apply through it, you earn referral points.</p>
                            <div class="flex gap-2 flex-col sm:flex-row">
                                <input id="referralLink" readonly class="input input-bordered w-full" value="/Jobportal/applicant/job_details.php?job_id=<?php echo $jobId; ?>&referrer=<?php echo $userId; ?>">
                                <button id="copyReferralLink" type="button" class="btn btn-primary">Copy Link</button>
                            </div>
                        </div>
                    <?php endif; ?>
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
                            <?php if ($hasSaved && !$hasApplied): ?>
                                <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $jobId; ?>" class="btn btn-sm btn-primary mt-3 inline-block">View Skill Gap</a>
                            <?php elseif (!$hasSaved && !$hasApplied): ?>
                                <p class="mt-3 text-slate-600">Save this job to view the full skill gap analysis.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Company Reviews Section -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Company Reviews</h2>
                    <?php if ($totalReviews > 0): ?>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <div class="rating rating-sm">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating-display" class="mask mask-star-2 bg-orange-400" <?php echo $i <= $averageRating ? 'checked' : ''; ?> disabled />
                                    <?php endfor; ?>
                                </div>
                                <span class="text-lg font-medium"><?php echo $averageRating; ?>/5</span>
                                <span class="text-slate-600">(<?php echo $totalReviews; ?> review<?php echo $totalReviews > 1 ? 's' : ''; ?>)</span>
                            </div>
                        </div>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php foreach ($reviews as $review): ?>
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="rating rating-sm">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <input type="radio" name="rating-<?php echo $review['Date_Submitted']; ?>" class="mask mask-star-2 bg-orange-400" <?php echo $i <= $review['Rating'] ? 'checked' : ''; ?> disabled />
                                                <?php endfor; ?>
                                            </div>
                                            <span class="font-medium text-sm"><?php echo $review['Is_Anonymous'] ? 'Anonymous' : htmlspecialchars($review['First_Name'] . ' ' . $review['Last_Name']); ?></span>
                                        </div>
                                        <span class="text-sm text-slate-600"><?php echo date('M j, Y', strtotime($review['Date_Submitted'])); ?></span>
                                    </div>
                                    <p class="text-slate-700"><?php echo htmlspecialchars($review['Feedback']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-600">No reviews yet. Be the first to review this company!</p>
                    <?php endif; ?>

                    <!-- Review Form -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Write a Review</h3>
                        <form id="reviewForm" class="space-y-4">
                            <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
                            <div>
                                <label class="block text-sm font-medium mb-2">Rating</label>
                                <div class="rating rating-lg">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" class="mask mask-star-2 bg-orange-400" required />
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div>
                                <label for="review_text" class="block text-sm font-medium mb-2">Your Review</label>
                                <textarea id="review_text" name="review_text" rows="4" class="textarea textarea-bordered w-full" placeholder="Share your experience working at this company..." required></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="is_anonymous" name="is_anonymous" class="checkbox" />
                                <label for="is_anonymous" class="text-sm font-medium cursor-pointer">Post review anonymously</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <?php if ($hasApplied): ?>
                        <div class="alert alert-success">
                            <span>You have already applied to this job.</span>
                        </div>
                    <?php else: ?>
                        <?php if ($canApply): ?>
                            <button type="button" class="btn btn-primary w-full" onclick="applyJob(<?php echo $jobId; ?><?php echo $referrerId ? ', ' . $referrerId : ''; ?>)">Apply Now</button>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <span>You are missing mandatory skills for this job. Save it and use Skill Gap Analysis.</span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($hasApplied): ?>
                        <!-- already applied: no save or skill gap actions -->
                    <?php elseif ($canApply): ?>
                        <button type="button" class="btn btn-primary w-full" onclick="applyJob(<?php echo $jobId; ?><?php echo $referrerId ? ', ' . $referrerId : ''; ?>)">Apply Now</button>
                    <?php else: ?>
                        <?php if ($hasSaved): ?>
                            <button type="button" class="btn btn-outline btn-error w-full" onclick="unsaveJob(<?php echo $jobId; ?>)">Remove from Saved</button>
                            <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $jobId; ?>" class="btn btn-secondary w-full">Skill Gap Analysis</a>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline w-full" onclick="saveJob(<?php echo $jobId; ?>)">Save Job</button>
                            <p class="text-slate-600 mt-3 text-center">Skill gap available after saving this job.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
    <script src="/Jobportal/applicant/review.js"></script>
    <script>
        document.getElementById('copyReferralLink')?.addEventListener('click', function() {
            const linkInput = document.getElementById('referralLink');
            if (!linkInput) return;
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(linkInput.value).then(function() {
                alert('Referral link copied!');
            });
        });
    </script>
</body>
</html>
