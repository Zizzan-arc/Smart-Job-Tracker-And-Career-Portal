<?php
include '../Database.php';

$companies = [];
$skills = [];
$categories = [];

$companyResult = $conn->query('SELECT Company_ID, Company_Name FROM company ORDER BY Company_Name');
if ($companyResult) {
    while ($row = $companyResult->fetch_assoc()) {
        $companies[] = $row;
    }
}

$categoryResult = $conn->query('SELECT Category_ID, Category_Name FROM category ORDER BY Category_Name');
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$skillResult = $conn->query('SELECT Skill_ID, Skill_Name FROM skill ORDER BY Skill_Name');
if ($skillResult) {
    while ($row = $skillResult->fetch_assoc()) {
        $skills[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Create Job Post</h1>
                <p class="text-slate-600">Add a new job for the portal.</p>
            </div>
            <a href="index.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-8 max-w-3xl">
            <form id="createJobForm" action="save_job.php" method="POST" class="space-y-6">
                <div>
                    <label class="label"><span class="label-text">Job Title</span></label>
                    <input id="jobTitle" name="job_title" type="text" class="input input-bordered w-full" placeholder="e.g. Frontend Developer" required>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="label"><span class="label-text">Base Salary</span></label>
                        <input id="baseSalary" name="base_salary" type="number" step="0.01" class="input input-bordered w-full" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Deadline</span></label>
                        <input id="deadline" name="deadline" type="date" class="input input-bordered w-full" required>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="label"><span class="label-text">Work Model</span></label>
                        <select id="workModel" name="work_model" class="select select-bordered w-full" required>
                            <option value="">Select model</option>
                            <option value="Remote">Remote</option>
                            <option value="Onsite">Onsite</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Employment Type</span></label>
                        <select id="employmentType" name="employment_type" class="select select-bordered w-full" required>
                            <option value="">Select type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label"><span class="label-text">Company</span></label>
                    <select id="companyId" name="company_id" class="select select-bordered w-full" required>
                        <option value="">Select a company</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['Company_ID']; ?>">
                                <?php echo htmlspecialchars($company['Company_Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="label"><span class="label-text">Categories</span></label>
                    <div class="grid gap-3 md:grid-cols-2">
                        <?php foreach ($categories as $category): ?>
                            <label class="flex items-center gap-3 border rounded-lg p-3 cursor-pointer">
                                <input type="checkbox" name="categories[]" value="<?php echo $category['Category_ID']; ?>" class="checkbox" />
                                <span><?php echo htmlspecialchars($category['Category_Name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="label"><span class="label-text">Required Skills</span></label>
                    <div class="grid gap-3 md:grid-cols-2">
                        <?php foreach ($skills as $skill): ?>
                            <label class="flex items-center gap-3 border rounded-lg p-3 cursor-pointer">
                                <input type="checkbox" name="required_skills[]" value="<?php echo $skill['Skill_ID']; ?>" class="checkbox" />
                                <span><?php echo htmlspecialchars($skill['Skill_Name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="label"><span class="label-text">Nice to Have Skills</span></label>
                    <div class="grid gap-3 md:grid-cols-2">
                        <?php foreach ($skills as $skill): ?>
                            <label class="flex items-center gap-3 border rounded-lg p-3 cursor-pointer">
                                <input type="checkbox" name="nice_skills[]" value="<?php echo $skill['Skill_ID']; ?>" class="checkbox" />
                                <span><?php echo htmlspecialchars($skill['Skill_Name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-slate-500 text-xs mt-1">Optional skills that are good to have but not required to apply.</p>
                </div>

                <div>
                    <label class="label"><span class="label-text">Other Required Skill</span></label>
                    <input type="text" name="other_skill" placeholder="Add a new required skill if needed" class="input input-bordered w-full" />
                    <p class="text-slate-500 text-xs mt-1">Enter one new required skill if it is not in the list.</p>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="jobs.php" class="btn btn-outline">View Jobs</a>
                    <button type="submit" class="btn btn-primary">Save Job</button>
                </div>
            </form>
        </div>
    </div>
    <script src="admin.js"></script>
</body>
</html>