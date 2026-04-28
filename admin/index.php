<?php
include '../Database.php';

$jobCount = 0;
$companyCount = 0;
$applicantCount = 0;
$applicationCount = 0;

$result = $conn->query('SELECT COUNT(*) AS c FROM jobpost');
if ($result) {
    $jobCount = $result->fetch_assoc()['c'];
}

$result = $conn->query('SELECT COUNT(*) AS c FROM company');
if ($result) {
    $companyCount = $result->fetch_assoc()['c'];
}

$result = $conn->query('SELECT COUNT(*) AS c FROM applicant');
if ($result) {
    $applicantCount = $result->fetch_assoc()['c'];
}

$result = $conn->query('SELECT COUNT(*) AS c FROM appliesto');
if ($result) {
    $applicationCount = $result->fetch_assoc()['c'];
}

$jobs = $conn->query(
    'SELECT j.Job_ID, j.Job_title, c.Company_name, j.Employment_Type, j.Deadline, COUNT(a.Application_ID) AS applications
     FROM jobpost j
     LEFT JOIN company c ON j.Company_ID = c.Company_ID
     LEFT JOIN appliesto a ON j.Job_ID = a.Job_ID
     GROUP BY j.Job_ID, j.Job_title, c.Company_name, j.Employment_Type, j.Deadline
     ORDER BY j.Job_ID DESC
     LIMIT 5'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                <p class="text-sm text-slate-600">Manage jobs, companies, and applicants from one place.</p>
            </div>
            <div class="flex gap-3">
                <a href="profile.php" class="btn btn-outline btn-primary">Admin Profile</a>
                <a href="../logout.php" class="btn btn-outline btn-error">Logout</a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4 mb-8">
            <div class="card bg-white shadow-sm p-6">
                <h2 class="text-lg font-semibold">Jobs</h2>
                <p class="text-3xl mt-4"><?php echo $jobCount; ?></p>
            </div>
            <div class="card bg-white shadow-sm p-6">
                <h2 class="text-lg font-semibold">Companies</h2>
                <p class="text-3xl mt-4"><?php echo $companyCount; ?></p>
            </div>
            <div class="card bg-white shadow-sm p-6">
                <h2 class="text-lg font-semibold">Applicants</h2>
                <p class="text-3xl mt-4"><?php echo $applicantCount; ?></p>
            </div>
            <div class="card bg-white shadow-sm p-6">
                <h2 class="text-lg font-semibold">Applications</h2>
                <p class="text-3xl mt-4"><?php echo $applicationCount; ?></p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 mb-8">
            <a href="create_job.php" class="btn btn-primary">Create Job</a>
            <a href="jobs.php" class="btn btn-secondary">View Jobs</a>
            <a href="view_applicants.php" class="btn btn-accent">View All Applicants</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold">Recent Jobs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Deadline</th>
                            <th>Applications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jobs && $jobs->num_rows): ?>
                            <?php while ($job = $jobs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $job['Job_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($job['Job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['Company_name'] ?: 'No company'); ?></td>
                                    <td><?php echo htmlspecialchars($job['Employment_Type']); ?></td>
                                    <td><?php echo htmlspecialchars($job['Deadline']); ?></td>
                                    <td><?php echo $job['applications']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-6">No jobs found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>