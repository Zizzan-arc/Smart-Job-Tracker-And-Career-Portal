// register_script.js - Client-side validation for registration form

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');

    // Form fields
    const firstName = document.getElementById('first_name');
    const lastName = document.getElementById('last_name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const experienceYears = document.getElementById('experience_years');
    const githubUrl = document.getElementById('github_url');
    const applicantFields = document.getElementById('applicantFields');
    const roleRadios = document.querySelectorAll('input[name="role"]');

    // Error elements
    const firstNameError = document.getElementById('firstNameError');
    const lastNameError = document.getElementById('lastNameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const experienceError = document.getElementById('experienceError');

    // Validation functions
    function validateFirstName() {
        if (firstName.value.trim() === '') {
            showError(firstNameError, 'First name is required.');
            return false;
        }
        hideError(firstNameError);
        return true;
    }

    function validateLastName() {
        if (lastName.value.trim() === '') {
            showError(lastNameError, 'Last name is required.');
            return false;
        }
        hideError(lastNameError);
        return true;
    }

    function validateEmail() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value.trim())) {
            showError(emailError, 'Please enter a valid email address.');
            return false;
        }
        hideError(emailError);
        return true;
    }

    function validatePassword() {
        if (password.value.length < 6) {
            showError(passwordError, 'Password must be at least 6 characters.');
            return false;
        }
        hideError(passwordError);
        return true;
    }

    function validateConfirmPassword() {
        if (password.value !== confirmPassword.value) {
            showError(confirmPasswordError, 'Passwords do not match.');
            return false;
        }
        hideError(confirmPasswordError);
        return true;
    }

    function validateExperience() {
        if (!isApplicantSelected()) {
            hideError(experienceError);
            return true;
        }

        const value = parseInt(experienceYears.value);
        if (isNaN(value) || value < 0) {
            showError(experienceError, 'Please enter valid experience years.');
            return false;
        }
        hideError(experienceError);
        return true;
    }

    function isApplicantSelected() {
        return Array.from(roleRadios).some(radio => radio.checked && radio.value === 'Applicant');
    }

    function updateApplicantFieldsVisibility() {
        if (isApplicantSelected()) {
            applicantFields.style.display = 'block';
            experienceYears.required = true;
        } else {
            applicantFields.style.display = 'none';
            experienceYears.required = false;
            experienceYears.value = '';
            hideError(experienceError);
        }
    }

    function showError(element, message) {
        element.textContent = message;
        element.classList.remove('hidden');
    }

    function hideError(element) {
        element.classList.add('hidden');
    }

    // Initial field visibility
    updateApplicantFieldsVisibility();

    // Event listeners for real-time validation
    firstName.addEventListener('blur', validateFirstName);
    lastName.addEventListener('blur', validateLastName);
    email.addEventListener('blur', validateEmail);
    password.addEventListener('blur', validatePassword);
    confirmPassword.addEventListener('blur', validateConfirmPassword);
    experienceYears.addEventListener('blur', validateExperience);

    roleRadios.forEach((radio) => {
        radio.addEventListener('change', updateApplicantFieldsVisibility);
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        const isValid = validateFirstName() && validateLastName() && validateEmail() &&
                        validatePassword() && validateConfirmPassword() && validateExperience();

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Disable button to prevent double submission
        registerBtn.disabled = true;
        registerBtn.textContent = 'Creating Account...';
    });

    // Check for server-side errors on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        // Display error (you can customize this)
        alert('Registration failed: ' + error);
    }
});