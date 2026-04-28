/**
 Script from index.html (Login Form)
 Handles client side verification before sending the data to PHP
 */

document.addEventListener('DOMContentLoaded', () => {

  const loginForm = document.getElementById('loginForm');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

  const emailError = document.getElementById('emailError');
  const passwordError = document.getElementById('passwordError');


  // passowrd visibility
  showPasswordCheckbox.addEventListener('change', () => {
    if (showPasswordCheckbox.checked) {
      passwordInput.type = 'text';
    } else {
      passwordInput.type = 'password';
    }
  });


  //  Validation Helpers

  function isValidEmail(value) {
    // Regular expression to check for basic email format
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  // basically removing  a class in the html tag
  function showError(element) {
    element.classList.remove('hidden');
  }

  // basically adding a class in the html tag
  function hideError(element) {
    element.classList.add('hidden');
  }


  emailInput.addEventListener('input', () => hideError(emailError));
  passwordInput.addEventListener('input', () => hideError(passwordError));


  // ─ Validating before sending the data to PHP ///
  loginForm.addEventListener('submit', (e) => {
    let hasError = false;

    // Email validation
    const emailValue = emailInput.value.trim();
    if (!emailValue || !isValidEmail(emailValue)) {
      showError(emailError);
      hasError = true;
    }

    // Password validation (Ensures it's not empty)
    if (!passwordInput.value) {
      showError(passwordError);
      hasError = true;
    }

    // If there's an error, prevent the form from sending to login_process.php
    if (hasError == true) {
      e.preventDefault();
    }
  });
});