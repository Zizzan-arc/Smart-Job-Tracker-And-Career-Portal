<?php
include '../Database.php';

$jobId = intval($_GET['job_id'] ?? 0);
$jobTitle = 'All Applicants';
$applicants = null;

if ($jobId > 0) {
    $jobResult = $conn->query("SELECT Job_title FROM jobpost WHERE Job_ID = $jobId");
    if ($jobResult && $jobResult->num_rows) {
        $jobTitle = 'Applicants for ' . $jobResult->fetch_assoc()['Job_title'];
    }
    $sql = "SELECT app.Application_date, app.Status, app.Referred_By, u.UserID, u.Email, ap.GitHub_URL, ap.Experience
            FROM appliesto app
            JOIN user u ON app.UserID = u.UserID
            LEFT JOIN applicant ap ON u.UserID = ap.UserID
            WHERE app.Job_ID = $jobId
            ORDER BY app.Application_date DESC";
    $applicants = $conn->query($sql);
} else {
    $sql = "SELECT app.Application_date, app.Status, app.Referred_By, u.UserID, u.Email, ap.GitHub_URL, ap.Experience, j.Job_title
            FROM appliesto app
            JOIN user u ON app.UserID = u.UserID
            LEFT JOIN applicant ap ON u.UserID = ap.UserID
            LEFT JOIN jobpost j ON app.Job_ID = j.Job_ID
            ORDER BY app.Application_date DESC";
    $applicants = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($jobTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($jobTitle); ?></h1>
                <p class="text-slate-600">View applicant applications and details.</p>
            </div>
            <div class="flex gap-3">
                <a href="jobs.php" class="btn btn-outline">Jobs</a>
                <a href="index.php" class="btn btn-secondary">Dashboard</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>GitHub</th>
                        <th>Experience</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Referred By</th>
                        <?php if ($jobId === 0): ?>
                            <th>Job Title</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($applicants && $applicants->num_rows): ?>
                        <?php while ($app = $applicants->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $app['UserID']; ?></td>
                                <td><?php echo htmlspecialchars($app['Email']); ?></td>
                                <td><?php echo htmlspecialchars($app['GitHub_URL'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($app['Experience'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($app['Application_date'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($app['Status'] ?? 'Pending'); ?></td>
                                <td><?php echo htmlspecialchars($app['Referred_By'] ?: 'N/A'); ?></td>
                                <?php if ($jobId === 0): ?>
                                    <td><?php echo htmlspecialchars($app['Job_title'] ?: 'Unknown'); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $jobId === 0 ? '8' : '7'; ?>" class="text-center py-8">No applications found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>