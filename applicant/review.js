// review.js - Handle company review submission

document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const rating = formData.get('rating');
    const reviewText = formData.get('review_text');
    const isAnonymous = document.getElementById('is_anonymous').checked ? 1 : 0;
    
    formData.set('is_anonymous', isAnonymous);

    if (!rating) {
        alert('Please select a rating.');
        return;
    }

    if (!reviewText.trim()) {
        alert('Please write a review.');
        return;
    }

    if (reviewText.trim().length < 10) {
        alert('Please write at least 10 characters in your review.');
        return;
    }

    fetch('/Jobportal/applicant/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Review submitted successfully!');
            location.reload(); // Refresh to show the new review
        } else {
            alert('Error submitting review: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the review.');
    });
});