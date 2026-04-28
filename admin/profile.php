<?php
include '../Database.php';

$admin = null;
$result = $conn->query("SELECT * FROM user WHERE Role = 'admin' LIMIT 1");
if ($result && $result->num_rows) {
    $admin = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Admin Profile</h1>
                <p class="text-slate-600">Edit basic admin details.</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="btn btn-outline">Dashboard</a>
                <a href="jobs.php" class="btn btn-secondary">Jobs</a>
            </div>
        </div>

        <?php if (!$admin): ?>
            <div class="bg-white rounded-xl shadow-sm p-8">
                <p class="text-red-500">No admin user found.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm p-8 max-w-2xl">
                <form id="adminProfileForm" action="/Jobportal/admin/save_profile.php" method="POST" class="space-y-6">
                    <input type="hidden" name="admin_id" value="<?php echo $admin['UserID']; ?>">

                    <div>
                        <label class="label"><span class="label-text">Email</span></label>
                        <input id="adminEmail" name="email" type="email" class="input input-bordered w-full" value="<?php echo htmlspecialchars($admin['Email']); ?>" required>
                    </div>

                    <div>
                        <label class="label"><span class="label-text">Password</span></label>
                        <input id="adminPassword" name="password" type="password" class="input input-bordered w-full" value="<?php echo htmlspecialchars($admin['Password']); ?>" required>
                    </div>

                    <div class="flex items-center gap-3">
                        <input id="showPassword" type="checkbox" class="checkbox" />
                        <label for="showPassword" class="cursor-pointer">Show password</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script src="admin.js"></script>
</body>
</html>