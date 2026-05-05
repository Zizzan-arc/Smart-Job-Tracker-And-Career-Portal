<?php
session_start();
require_once '../config.php';

$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;
if (!$userId) {
    header('Location: ../index.html');
    exit();
}

$professionalVersion = '';
$errorMessage = '';
$rawCv = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawCv = trim($_POST['raw_cv'] ?? '');
    if ($rawCv === '') {
        $errorMessage = 'Please paste your resume text into the box before submitting.';
    } else {
        $prompt = "Please professionalize this resume text. Format it with clear headings and bullet points. Also, at the very end, list the top 5 technical skills you identified.\n\nResume Text:\n" . $rawCv;

        $payload = json_encode([
            'model' => 'llama-3.1-8b-instant',
            'input' => $prompt,
            'max_output_tokens' => 1024,
            'temperature' => 0.2,
        ]);

        $ch = curl_init('https://api.groq.com/v1/models/llama-3.1-8b-instant/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $curlError) {
            $errorMessage = 'Unable to contact the AI service. Please try again later.';
        } else {
            $responseData = json_decode($response, true);
            if (!is_array($responseData)) {
                $errorMessage = 'Unexpected response from the AI service.';
            } else {
                if (!empty($responseData['output'][0]['content'][0]['text'])) {
                    $professionalVersion = trim($responseData['output'][0]['content'][0]['text']);
                } elseif (!empty($responseData['output'][0]['content'][0])) {
                    $professionalVersion = trim($responseData['output'][0]['content'][0]);
                } elseif (!empty($responseData['choices'][0]['text'])) {
                    $professionalVersion = trim($responseData['choices'][0]['text']);
                } else {
                    $errorMessage = 'Could not extract the AI response. Please try again.';
                }

                if ($httpStatus >= 400 && $errorMessage === '') {
                    $errorMessage = 'AI service returned HTTP ' . $httpStatus . '. Please try again later.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">AI CV Refiner</h1>
                <p class="text-slate-600">Paste your resume text below and get a polished professional version.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="/Jobportal/applicant/applicant_dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/Jobportal/applicant/browse_jobs.php" class="btn btn-secondary">Browse Jobs</a>
                <a href="/Jobportal/applicant/saved_jobs.php" class="btn btn-primary">Saved Jobs</a>
                <a href="/Jobportal/logout.php" class="btn btn-error btn-outline">Logout</a>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Paste Your Resume</h2>
                <form method="POST" action="cv_workspace.php" class="space-y-4">
                    <div>
                        <label class="label"><span class="label-text">Raw Resume Text</span></label>
                        <textarea name="raw_cv" rows="18" class="textarea textarea-bordered w-full" placeholder="Paste your resume text here..."><?php echo htmlspecialchars($rawCv); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Refine Resume</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Professional Version</h2>
                <div class="min-h-[420px] rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-800 whitespace-pre-wrap">
                    <?php echo $professionalVersion ? nl2br(htmlspecialchars($professionalVersion)) : '<span class="text-slate-500">Refined resume output will appear here after submission.</span>'; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
