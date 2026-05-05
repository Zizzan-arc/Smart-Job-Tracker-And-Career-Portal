
<?php

session_start();
include '../Database.php';

// Check if the same user is logged in from registration
if (!isset($_SESSION['user_id'])) {
    header('Location: ../registration/register.html');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch all skills from Skill table
$sql = "SELECT Skill_ID, Skill_name FROM Skill";
$result = $conn->query($sql);

// storing the skills from database into an array (basically importing the skills so that I can use in the HTML part)
$skillsarray = [];

// populating the skillsarray with the data from database. 
if ($result->num_rows > 0) {
    // row = {"key": value} pair
    while ($row = $result->fetch_assoc()) {
        $skillsarray[] = $row;
    }
} else {
    echo "No skills found in the database.";
    
    exit;
}
?>

<!-- HTML PART -->

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Select Skills — JobPortal</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
</head>

<body class="gradient-bg min-h-screen font-sans flex items-center justify-center p-4 relative overflow-y-auto">

    <div class="blob w-72 h-72 bg-indigo-500 -top-20 -left-20 float-anim" style="animation-delay:0s;"></div>
    <div class="blob w-96 h-96 bg-purple-600 bottom-0 right-0 float-anim" style="animation-delay:3s;"></div>
    <div class="blob w-64 h-64 bg-cyan-400 top-1/3 right-1/4 float-anim" style="animation-delay:1.5s;"></div>

    <main class="w-full max-w-2xl z-10">
        <div class="card-glass rounded-2xl shadow-2xl p-8 sm:p-10 transition-all duration-500 hover:shadow-indigo-500/10 hover:shadow-3xl">

            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Select Your Skills</h1>
                <p class="text-slate-400 mt-1 text-sm">Choose the skills that match your expertise</p>
            </div>

            <form id="skillsForm" action="/Jobportal/onboarding/save_skills.php" method="POST" class="space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- PHP for each loop -->
                    <?php foreach ($skillsarray as $skill): ?>
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-slate-700 bg-slate-800/60 hover:bg-slate-700/60 transition-colors duration-200">
                        <!-- in input tag, the value thing returns the ID to the system -->
                        <input type="checkbox" name="selected_skills[]" value="
                          <?php echo $skill['Skill_ID']; ?>" 
                            class="checkbox checkbox-sm checkbox-primary border-slate-600" />
                        <span class="text-slate-300 font-medium text-sm"><?php echo htmlspecialchars($skill['Skill_name']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div>
                    <label class="label"><span class="label-text text-slate-300">Other Skill</span></label>
                    <input type="text" name="other_skill" placeholder="Enter a skill not listed" class="input input-bordered w-full bg-slate-800/60 border-slate-700 text-white placeholder:text-slate-500" />
                    <p class="text-slate-500 text-xs mt-1">Add one new skill if it is not already in the list.</p>
                </div>
                 <!-- Skill Error Message -->
                <p id="skillsError" class="text-error text-xs mt-1 hidden">Please select at least one skill or enter an other skill.</p>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 border-none hover:from-indigo-600 hover:to-purple-700">
                        Continue
                    </button>
                    <!-- <a href="/Jobportal/index.html" class="btn btn-outline flex-1">
                        Skip for now
                    </a> -->
                </div>

            </form>

        </div>
    </main>

    <script src="skills.js"></script>
</body>
</html>
