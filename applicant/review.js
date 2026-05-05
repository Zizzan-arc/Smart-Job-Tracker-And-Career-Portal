document.getElementById('reviewForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // grabs all the inputs inside the form and packs them
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
    .then(response => response.text()) // Get simple text back instead of JSON
    .then(message => {
        if (message.trim() === 'Success') {
            alert('Review submitted successfully!');
            location.reload(); 
        } else {
            alert(message); // Show the message (or error) from PHP
        }
    });
});