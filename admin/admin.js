document.addEventListener('DOMContentLoaded', function () {
    const createJobForm = document.getElementById('createJobForm');
    if (createJobForm) {
        createJobForm.addEventListener('submit', function (event) {
            const title = document.getElementById('jobTitle').value.trim();
            const salary = document.getElementById('baseSalary').value.trim();
            const deadline = document.getElementById('deadline').value;
            const company = document.getElementById('companyId').value;
            const skills = document.querySelectorAll('input[name="required_skills[]"]:checked');

            if (!title || !salary || !deadline || company === '') {
                alert('Please complete all required job fields.');
                event.preventDefault();
                return;
            }

            const categories = document.querySelectorAll('input[name="categories[]"]:checked');
            if (categories.length === 0) {
                alert('Please select at least one category.');
                event.preventDefault();
                return;
            }

            if (skills.length === 0) {
                alert('Please select at least one required skill.');
                event.preventDefault();
            }
        });
    }

    const showPassword = document.getElementById('showPassword');
    if (showPassword) {
        showPassword.addEventListener('change', function () {
            const passwordInput = document.getElementById('adminPassword');
            if (passwordInput) {
                passwordInput.type = this.checked ? 'text' : 'password';
            }
        });
    }
});