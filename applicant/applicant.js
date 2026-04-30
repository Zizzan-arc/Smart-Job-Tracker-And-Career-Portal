function applyJob(jobId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/Jobportal/applicant/apply_job.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Application submitted successfully!');
            location.reload();
        } else {
            alert('Error submitting application. Please try again.');
        }
    };
    
    xhr.send('job_id=' + jobId);
}

function saveJob(jobId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/Jobportal/applicant/save_job.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Job saved successfully!');
            location.reload();
        } else {
            alert('Error saving job. Please try again.');
        }
    };
    
    xhr.send('job_id=' + jobId + '&action=save');
}

function unsaveJob(jobId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/Jobportal/applicant/save_job.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Job removed from saved list!');
            location.reload();
        } else {
            alert('Error removing job. Please try again.');
        }
    };
    
    xhr.send('job_id=' + jobId + '&action=unsave');
}
