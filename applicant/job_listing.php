<?php
session_start();
include '../Database.php';

// Get user ID
$userId = $_SESSION['current_user_id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../index.html');
    exit();
}

// Get filter values from URL
$searchJob = $_GET['search'] ?? '';
$filterCompany = $_GET['company'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$jobId = $_GET['job_id'] ?? null;

$skillIds = [];
$skillResult = $conn->query("SELECT Skill_ID FROM Has_Skill WHERE UserID = $userId");
while ($row = $skillResult->fetch_assoc()) {
    $skillIds[] = intval($row['Skill_ID']);
}
$skillList = !empty($skillIds) ? implode(',', $skillIds) : '0';

// Build the WHERE clause for filtering
$whereConditions = [];

if (!empty($searchJob)) {
    $whereConditions[] = "j.Job_title LIKE '%$searchJob%'";
}

if (!empty($filterCompany)) {
    $filterCompany = intval($filterCompany);
    $whereConditions[] = "j.Company_ID = $filterCompany";
}

if (!empty($filterCategory)) {
    $filterCategory = intval($filterCategory);
    $whereConditions[] = "jc.Category_ID = $filterCategory";
}

if ($jobId) {
    $jobId = intval($jobId);
    $whereConditions[] = "j.Job_ID = $jobId";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get all jobs with filters
$jobsQuery = "SELECT j.Job_ID, j.Job_title, j.Base_salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name, c.Company_ID,
                     GROUP_CONCAT(DISTINCT cat.Category_name SEPARATOR ', ') as categories,
                     COUNT(DISTINCT CASE WHEN rs.Skill_ID IN ($skillList) THEN rs.Skill_ID END) AS match_count,
                     COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 THEN rs.Skill_ID END) AS total_mandatory,
                     COUNT(DISTINCT CASE WHEN rs.Is_Mandatory = 1 AND rs.Skill_ID IN ($skillList) THEN rs.Skill_ID END) AS matched_mandatory
              FROM JobPost j
              LEFT JOIN Company c ON j.Company_ID = c.Company_ID
              LEFT JOIN Job_Category jc ON j.Job_ID = jc.Job_ID
              LEFT JOIN Category cat ON jc.Category_ID = cat.Category_ID
              LEFT JOIN Requires_Skill rs ON j.Job_ID = rs.Job_ID
              $whereClause
              GROUP BY j.Job_ID, j.Job_title, j.Base_salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name, c.Company_ID
              ORDER BY j.Job_ID DESC";

$jobsResult = $conn->query($jobsQuery);
$jobs = [];
while ($job = $jobsResult->fetch_assoc()) {
    $jobs[] = $job;
}

// Get list of all companies for filter dropdown
$companiesResult = $conn->query("SELECT Company_ID, Company_name FROM Company ORDER BY Company_name");
$companies = [];
while ($company = $companiesResult->fetch_assoc()) {
    $companies[] = $company;
}

// Get list of all categories for filter dropdown
$categoriesResult = $conn->query("SELECT Category_ID, Category_name FROM Category ORDER BY Category_name");
$categories = [];
while ($category = $categoriesResult->fetch_assoc()) {
    $categories[] = $category;
}

// Get applicant's applied jobs
$appliedResult = $conn->query("SELECT Job_ID FROM appliesto WHERE UserID = $userId");
$appliedJobs = [];
while ($row = $appliedResult->fetch_assoc()) {
    $appliedJobs[] = $row['Job_ID'];
}

// Get applicant's saved jobs
$savedResult = $conn->query("SELECT Job_ID FROM Wishlist WHERE UserID = $userId");
$savedJobs = [];
while ($row = $savedResult->fetch_assoc()) {
    $savedJobs[] = $row['Job_ID'];
}
?>
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
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Browse Jobs</h1>
                <p class="text-slate-600">Find your next opportunity</p>
            </div>
            <div class="flex gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/logout.php" class="btn btn-outline btn-error">Logout</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Filters</h2>
            <form id="filterForm" method="GET">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="label"><span class="label-text">Search by Job Title</span></label>
                        <input type="text" id="searchInput" name="search" class="input input-bordered w-full" placeholder="e.g. Developer" value="<?php echo htmlspecialchars($searchJob); ?>">
                    </div>

                    <div>
                        <label class="label"><span class="label-text">Company</span></label>
                        <select id="companySelect" name="company" class="select select-bordered w-full">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['Company_ID']; ?>" <?php echo $filterCompany == $company['Company_ID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['Company_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="label"><span class="label-text">Category</span></label>
                        <select id="categorySelect" name="category" class="select select-bordered w-full">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['Category_ID']; ?>" <?php echo $filterCategory == $category['Category_ID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['Category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary w-full">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Jobs List -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Jobs (<?php echo count($jobs); ?> found)</h2>
            <?php if (!empty($jobs)): ?>
                <div class="space-y-4">
                    <?php foreach ($jobs as $job): ?>
                        <div class="border rounded-lg p-4 hover:bg-slate-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($job['Job_title']); ?></h3>
                                    <p class="text-slate-600 font-medium"><?php echo htmlspecialchars($job['Company_name'] ?: 'Company'); ?></p>
                                    
                                    <?php if (!empty($job['categories'])): ?>
                                        <div class="mt-2">
                                            <?php foreach (explode(', ', $job['categories']) as $cat): ?>
                                                <span class="badge badge-outline text-xs"><?php echo htmlspecialchars($cat); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex flex-wrap gap-4 text-sm text-slate-600 mt-3">
                                        <span>💰 <?php echo number_format($job['Base_salary'], 0); ?></span>
                                        <span>🏢 <?php echo htmlspecialchars($job['Work_Model']); ?></span>
                                        <span>⏰ <?php echo htmlspecialchars($job['Employment_Type']); ?></span>
                                        <span>📅 Deadline: <?php echo htmlspecialchars($job['Deadline']); ?></span>
                                    </div>
                                </div>

                                <div class="ml-4 flex flex-col gap-2 items-end">
                                    <a href="/Jobportal/applicant/job_details.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-outline btn-sm">View Details</a>
                                    <?php $canApply = ($job['match_count'] > 0 && ($job['total_mandatory'] == 0 || $job['matched_mandatory'] == $job['total_mandatory'])); ?>
                                    <?php if (in_array($job['Job_ID'], $savedJobs) || in_array($job['Job_ID'], $appliedJobs)): ?>
                                        <a href="/Jobportal/applicant/skill_gap.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-secondary btn-sm">Skill Gap</a>
                                    <?php endif; ?>
                                    <?php if (in_array($job['Job_ID'], $appliedJobs)): ?>
                                        <span class="badge badge-success">Applied</span>
                                    <?php else: ?>
                                        <?php if ($canApply): ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="applyJob(<?php echo $job['Job_ID']; ?>)">Apply Now</button>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Mandatory skills missing</span>
                                        <?php endif; ?>
                                        <?php if (in_array($job['Job_ID'], $savedJobs)): ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="unsaveJob(<?php echo $job['Job_ID']; ?>)">Unsave</button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="saveJob(<?php echo $job['Job_ID']; ?>)">Save Job</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-600 text-center py-8">No jobs found matching your filters. Try adjusting your search.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="/Jobportal/applicant/applicant.js"></script>
</body>
</html>
