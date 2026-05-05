// Simple function to apply for a job
function applyJob(jobId, referrerId = null) {
    let data = "job_id=" + jobId;
    if (referrerId) {
        data += "&referrer=" + referrerId;
    }

    fetch('/Jobportal/applicant/apply_job.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data
    })
    .then(response => {
        if (response.ok) {
            alert('Application submitted successfully!');
            location.reload();
        } else {
            alert('Error submitting application.');
        }
    });
}

// Simple function to save or unsave a job
function saveJob(jobId) {
    fetch('/Jobportal/applicant/save_job.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "job_id=" + jobId + "&action=save"
    })
    .then(response => {
        if (response.ok) {
            alert('Job saved successfully!');
            location.reload();
        } else {
            alert('Error saving job.');
        }
    });
}

function unsaveJob(jobId) {
    fetch('/Jobportal/applicant/save_job.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "job_id=" + jobId + "&action=unsave"
    })
    .then(response => {
        if (response.ok) {
            alert('Job removed from saved list!');
            location.reload();
        } else {
            alert('Error removing job.');
        }
    });
}
