<?php
require_once __DIR__ . '/auth.php';
include '../Database.php';

$userId = intval($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    header('Location: view_applicants.php');
    exit;
}

$userResult = $conn->query(
    "SELECT u.UserID, u.First_Name, u.Last_Name, u.Email, a.GitHub_URL, a.Experience_Years, a.Referral_Points
     FROM User u
     LEFT JOIN Applicant a ON u.UserID = a.UserID
     WHERE u.UserID = $userId
     LIMIT 1"
);
if (!$userResult || $userResult->num_rows === 0) {
    header('Location: view_applicants.php');
    exit;
}
$user = $userResult->fetch_assoc();

$skills = [];
$skillResult = $conn->query(
    "SELECT s.Skill_Name
     FROM Has_Skill hs
     JOIN Skill s ON hs.Skill_ID = s.Skill_ID
     WHERE hs.UserID = $userId"
);
while ($row = $skillResult->fetch_assoc()) {
    $skills[] = $row['Skill_Name'];
}

$appliedJobs = [];
$applicationResult = $conn->query(
    "SELECT j.Job_Title, j.Deadline, a.Status, a.Application_date
     FROM appliesto a
     JOIN JobPost j ON a.Job_ID = j.Job_ID
     WHERE a.UserID = $userId
     ORDER BY a.Application_date DESC"
);
while ($row = $applicationResult->fetch_assoc()) {
    $appliedJobs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($user['First_Name'] . ' ' . $user['Last_Name']); ?></h1>
                <p class="text-slate-600">Applicant profile and skillset.</p>
            </div>
            <div class="flex gap-3">
                <a href="view_applicants.php" class="btn btn-outline">Back</a>
                <a href="index.php" class="btn btn-secondary">Dashboard</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Contact</h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                <p><strong>GitHub:</strong> <?php echo htmlspecialchars($user['GitHub_URL'] ?: 'N/A'); ?></p>
                <p><strong>Experience:</strong> <?php echo htmlspecialchars($user['Experience_Years'] ?? '0'); ?> years</p>
                <p><strong>Referral Points:</strong> <?php echo htmlspecialchars($user['Referral_Points'] ?? '0'); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Skills</h2>
                <?php if (!empty($skills)): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($skills as $skill): ?>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-600">No skills have been added yet.</p>
                <?php endif; ?>

                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-3">Applications</h3>
                    <?php if (!empty($appliedJobs)): ?>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Job</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appliedJobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['Job_Title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['Status']); ?></td>
                                            <td><?php echo htmlspecialchars($job['Application_date']); ?></td>
                                            <td><?php echo htmlspecialchars($job['Deadline']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-600">This applicant has not applied to any jobs yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
