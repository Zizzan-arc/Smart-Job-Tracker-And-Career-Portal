<?php
session_start();
include '../Database.php';

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$searchTitle = trim($_GET['search'] ?? '');
$filterCompany = intval($_GET['company'] ?? 0);
$filterCategory = intval($_GET['category'] ?? 0);
$jobId = intval($_GET['job_id'] ?? 0);

$skillIds = [];
$skillResult = $conn->query("SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId");
while ($row = $skillResult->fetch_assoc()) {
    $skillIds[] = intval($row['Skill_ID']);
}
$skillList = !empty($skillIds) ? implode(',', $skillIds) : '0';

$where = [];
if ($searchTitle !== '') {
    $where[] = "j.Job_Title LIKE '%" . $conn->real_escape_string($searchTitle) . "%'";
}
if ($filterCompany > 0) {
    $where[] = "j.Company_ID = $filterCompany";
}
if ($filterCategory > 0) {
    $where[] = "jc.Category_ID = $filterCategory";
}
if ($jobId > 0) {
    $where[] = "j.Job_ID = $jobId";
}
$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$jobsQuery = "SELECT j.Job_ID, j.Job_Title, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name,
                     GROUP_CONCAT(DISTINCT cat.Category_name SEPARATOR ', ') AS categories,
                     COUNT(DISTINCT CASE WHEN rs.Skill_ID IN ($skillList) THEN rs.Skill_ID END) AS match_count,
                     COUNT(DISTINCT CASE WHEN rs.Skill_ID NOT IN ($skillList) THEN rs.Skill_ID END) AS missing_count,
                     SUM(CASE WHEN rs.Is_Mandatory = 1 THEN 1 ELSE 0 END) AS total_mandatory,
                     SUM(CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID IN ($skillList) THEN 1 ELSE 0 END) AS matched_mandatory,
                     IFNULL(r.review_count, 0) AS review_count,
                     IFNULL(r.avg_rating, 0) AS avg_rating
              FROM JobPost j
              LEFT JOIN Company c ON j.Company_ID = c.Company_ID
              LEFT JOIN Job_Category jc ON j.Job_ID = jc.Job_ID
              LEFT JOIN Category cat ON jc.Category_ID = cat.Category_ID
              LEFT JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
              LEFT JOIN (
                  SELECT Company_ID, COUNT(*) AS review_count, ROUND(AVG(Rating), 1) AS avg_rating
                  FROM leave_review
                  GROUP BY Company_ID
              ) r ON c.Company_ID = r.Company_ID
              $whereClause
              GROUP BY j.Job_ID, j.Job_Title, j.Base_Salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name, r.review_count, r.avg_rating
              HAVING match_count > 0
              ORDER BY j.Job_ID DESC";

$jobsResult = $conn->query($jobsQuery);
$jobs = [];
while ($row = $jobsResult->fetch_assoc()) {
    $jobs[] = $row;
}

$companiesResult = $conn->query("SELECT Company_ID, Company_name FROM Company ORDER BY Company_name");
$companies = [];
while ($row = $companiesResult->fetch_assoc()) {
    $companies[] = $row;
}

$categoriesResult = $conn->query("SELECT Category_ID, Category_name FROM Category ORDER BY Category_name");
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

$appliedResult = $conn->query("SELECT Job_ID FROM appliesto WHERE UserID = $userId");
$appliedJobs = [];
while ($row = $appliedResult->fetch_assoc()) {
    $appliedJobs[] = $row['Job_ID'];
}

$savedResult = $conn->query("SELECT Job_ID FROM Wishlist WHERE UserID = $userId");
$savedJobs = [];
while ($row = $savedResult->fetch_assoc()) {
    $savedJobs[] = $row['Job_ID'];
}
?>

<!-- html part -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Browse Jobs</h1>
                <p class="text-slate-600">Filter by position, category, or company.</p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/applicant/saved_jobs.php" class="btn btn-secondary">Saved Jobs</a>
                <a href="/Jobportal/applicant/applied_jobs.php" class="btn btn-primary">Applied Jobs</a>
                <a href="/Jobportal/logout.php" class="btn btn-error btn-outline">Logout</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
            <aside class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Filters</h2>
                <form method="GET" action="browse_jobs.php" class="space-y-4">
                    <div>
                        <label class="label"><span class="label-text">Position</span></label>
                        <input type="text" name="search" class="input input-bordered w-full" placeholder="Job title" value="<?php echo htmlspecialchars($searchTitle); ?>">
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Company</span></label>
                        <!-- using select because it will give a drop down menu -->
                        <select name="company" class="select select-bordered w-full">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['Company_ID']; ?>" <?php echo $filterCompany == $company['Company_ID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($company['Company_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Category</span></label>
                        <select name="category" class="select select-bordered w-full">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['Category_ID']; ?>" <?php echo $filterCategory == $category['Category_ID'] ? 'selected' : ''; ?>> <?php echo htmlspecialchars($category['Category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Apply Filters</button>
                    <a href="browse_jobs.php" class="btn btn-outline w-full">Clear Filters</a>
                </form>
            </aside>

            <main>
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold">Jobs Found</h2>
                    <p class="text-slate-600 mt-2"><?php echo count($jobs); ?> job(s) matching your search.</p>
                </div>

                <?php if (!empty($jobs)): ?>
                    <div class="space-y-4">
                        <?php foreach ($jobs as $job): ?>
                            <div class="border rounded-lg p-6 hover:bg-slate-50">
                                <div class="flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-start">
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($job['Job_Title']); ?>
                                    </h3>
                                        <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                        <?php if (!empty($job['categories'])): ?>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <?php foreach (explode(', ', $job['categories']) as $cat): ?>
                                                    <span class="badge badge-outline text-xs"><?php echo htmlspecialchars($cat); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                                            <span>💰 <?php echo number_format($job['Base_Salary'], 0); ?></span>
                                            <span>🏢 <?php echo htmlspecialchars($job['Work_Model']); ?></span>
                                            <span>⏰ <?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                            <span>📅 Deadline: <?php echo htmlspecialchars($job['Deadline']); ?></span>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2 items-center text-sm">
                                            <?php if ($job['match_count'] > 0): ?>
                                                <span class="badge badge-success"><?php echo intval($job['match_count']); ?> skill(s) match</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">No matching skills — target learning job</span>
                                            <?php endif; ?>
                                            <?php if ($job['review_count'] > 0): ?>
                                                <span class="badge badge-info"><?php echo number_format($job['avg_rating'],1); ?>★ (<?php echo intval($job['review_count']); ?> reviews)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Job-details button -->
                                    <div class="flex flex-col gap-3 lg:items-end">
                                        <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary">View Details</a>
                                        <?php $canApply = ($job['match_count'] > 0 && ($job['total_mandatory'] == 0 || $job['matched_mandatory'] == $job['total_mandatory'])); ?>
                                        <?php if (in_array($job['Job_ID'], $appliedJobs)): ?>
                                            <span class="badge badge-success">Applied</span>
                                            <?php if (in_array($job['Job_ID'], $savedJobs)): ?>
                                                <button type="button" class="btn btn-outline btn-error" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Remove</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($canApply): ?>
                                                <button type="button" class="btn btn-primary" onclick="applyJob(<?php echo $job['Job_ID']; ?>)">Apply</button>
                                                <?php if (in_array($job['Job_ID'], $savedJobs)): ?>
                                                    <button type="button" class="btn btn-outline btn-error" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Remove</button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($job['match_count'] == 0): ?>
                                                    <span class="badge badge-warning">No matching skills</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Mandatory skills missing</span>
                                                <?php endif; ?>
                                                <?php if (in_array($job['Job_ID'], $savedJobs)): ?>
                                                    <button type="button" class="btn btn-outline btn-error" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Unsave</button>
                                                    <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary">Skill Gap</a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline" onclick="saveJob(<?php echo $job['Job_ID']; ?>)">Save Job</button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-slate-600">
                        No jobs found. 
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
