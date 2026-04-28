document.addEventListener('DOMContentLoaded', () => {
  const registerForm = document.getElementById('registerForm');
  const roleRadios = document.querySelectorAll('input[name="user_role"]');

  // Select the form-control divs for GitHub and Experience fields
  const githubField = document.getElementById('githubSection');
  const experienceField = document.getElementById('experienceSection');

  const firstNameInput = document.getElementById('firstName');
  const lastNameInput = document.getElementById('lastName');
  const emailInput = document.getElementById('email');
  const githubUrlInput = document.getElementById('githubUrl');
  const experienceInput = document.getElementById('experience');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

  // --- Dynamic UI: Hide/Show fields based on Role ---
  roleRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'Admin') {
        githubField.classList.add('hidden'); // Hide GitHub for Admins
        experienceField.classList.add('hidden'); // Hide Experience for Admins
      } else {
        githubField.classList.remove('hidden'); // Show for Applicants
        experienceField.classList.remove('hidden'); // Show Experience for Applicants
      }
    });
  });


   // passowrd visibility
  showPasswordCheckbox.addEventListener('change', () => {
    if (showPasswordCheckbox.checked) {
      passwordInput.type = 'text';
      confirmPasswordInput.type = 'text';
    } else {
      // hides the password
      passwordInput.type = 'password';
      confirmPasswordInput.type = 'password';
    }
  });


  // --- Helper Functions ---
  const isValidEmail = (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
  const isValidUrl = (val) => {
    try {
      new URL(val);
      return true;
    } catch {
      return false;
    }
  };

  // Arrow Functions for showing/hiding errors
  const showError = (el) => el.classList.remove('hidden');
  const hideError = (el) => el.classList.add('hidden');

  // Clear errors on typing
  [firstNameInput, lastNameInput, emailInput, githubUrlInput, experienceInput, passwordInput, confirmPasswordInput].forEach(input => {
    input.addEventListener('input', () => {
      const errorId = input.id + 'Error';
      const errorEl = document.getElementById(errorId);
      if (errorEl) hideError(errorEl);
    });
  });

  // --- Form Validation ---
  registerForm.addEventListener('submit', (e) => {
    let hasError = false;
    const selectedRole = document.querySelector('input[name="user_role"]:checked');

    // Check role selection
    if (!selectedRole) {
      showError(document.getElementById('roleError'));
      hasError = true;
    } else {
      hideError(document.getElementById('roleError'));
    }

    if (!firstNameInput.value.trim()) { showError(document.getElementById('firstNameError')); hasError = true; }
    if (!lastNameInput.value.trim()) { showError(document.getElementById('lastNameError')); hasError = true; }

    if (!isValidEmail(emailInput.value.trim())) {
      showError(document.getElementById('emailError'));
      hasError = true;
    }

    // Validate GitHub URL and Experience only if Applicant is selected
    if (selectedRole && selectedRole.value === 'Applicant') {
      if (!isValidUrl(githubUrlInput.value.trim())) {
        showError(document.getElementById('githubUrlError'));
        hasError = true;
      }
      if (!experienceInput.value.trim()) {
        showError(document.getElementById('experienceError'));
        hasError = true;
      }
    }

    if (passwordInput.value.length < 6) {
      showError(document.getElementById('passwordError'));
      hasError = true;
    }

    if (confirmPasswordInput.value !== passwordInput.value || !confirmPasswordInput.value) {
      showError(document.getElementById('confirmPasswordError'));
      hasError = true;
    }

    if (hasError) e.preventDefault();
  });
});