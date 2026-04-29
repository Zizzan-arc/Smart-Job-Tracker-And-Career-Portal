<?php
include '../Database.php';

$jobs = $conn->query(
    'SELECT j.Job_ID, j.Job_title, j.Base_salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name,
            COALESCE(GROUP_CONCAT(DISTINCT cat.Category_name ORDER BY cat.Category_name SEPARATOR ", "), "Uncategorized") AS categories,
            COUNT(a.Application_ID) AS application_count
     FROM JobPost j
     LEFT JOIN Company c ON j.Company_ID = c.Company_ID
     LEFT JOIN Job_Category jc ON j.Job_ID = jc.Job_ID
     LEFT JOIN Category cat ON jc.Category_ID = cat.Category_ID
     LEFT JOIN appliesto a ON j.Job_ID = a.Job_ID
     GROUP BY j.Job_ID, j.Job_title, j.Base_salary, j.Work_Model, j.Employment_Type, j.Deadline, c.Company_name
     ORDER BY j.Job_ID DESC'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Jobs</h1>
                <p class="text-slate-600">All posted jobs and application counts.</p>
            </div>
            <div class="flex gap-3">
                <!-- going into the home page -->
                <a href="index.php" class="btn btn-outline">Dashboard</a>
                <!-- going into the create job page -->
                <a href="create_job.php" class="btn btn-primary">Create Job</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Categories</th>
                        <th>Type</th>
                        <th>Deadline</th>
                        <th>Applications</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jobs && $jobs->num_rows): ?>
                        <?php while ($job = $jobs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $job['Job_ID']; ?></td>
                                <td><?php echo htmlspecialchars($job['Job_title']); ?></td>
                                <td><?php echo htmlspecialchars($job['Company_name'] ?: 'No company'); ?></td>
                                <td><?php echo htmlspecialchars($job['categories']); ?></td>
                                <td><?php echo htmlspecialchars($job['Employment_Type']); ?></td>
                                <td><?php echo htmlspecialchars($job['Deadline']); ?></td>
                                <td><?php echo $job['application_count']; ?></td>
                                <td>
                                    <a href="view_applicants.php?job_id=<?php echo $job['Job_ID']; ?>" class="btn btn-xs btn-info">Applicants</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-8">No jobs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>