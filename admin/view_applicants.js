document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('.status-select');

    selects.forEach(function (select) {
        select.addEventListener('change', function () {
            const userId = this.dataset.userId;
            const jobId = this.dataset.jobId;
            const status = this.value;
            updateApplicationStatus(userId, jobId, status, this);
        });
    });
});

function updateApplicationStatus(userId, jobId, status, selectElement) {
    const request = new XMLHttpRequest();
    request.open('POST', 'update_application_status.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    request.onload = function () {
        if (request.status === 200) {
            const response = JSON.parse(request.responseText);
            if (response.success) {
                showTemporaryMessage(selectElement, 'Status updated', 'success');
            } else {
                showTemporaryMessage(selectElement, response.message || 'Update failed', 'error');
            }
        } else {
            showTemporaryMessage(selectElement, 'Update failed', 'error');
        }
    };

    request.send('user_id=' + encodeURIComponent(userId) + '&job_id=' + encodeURIComponent(jobId) + '&status=' + encodeURIComponent(status));
}

function showTemporaryMessage(element, message, type) {
    const messageEl = document.createElement('div');
    messageEl.textContent = message;
    messageEl.className = type === 'success' ? 'text-xs text-success' : 'text-xs text-error';
    messageEl.style.marginTop = '4px';
    element.parentNode.appendChild(messageEl);
    setTimeout(() => messageEl.remove(), 3000);
}
