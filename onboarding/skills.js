document.addEventListener('DOMContentLoaded', () => {
  const skillsForm = document.getElementById('skillsForm');
  const skillsError = document.getElementById('skillsError');

  skillsForm.addEventListener('submit', (e) => {
    const checkboxes = document.querySelectorAll('input[name="selected_skills[]"]:checked');

    if (checkboxes.length === 0) {
      e.preventDefault();
      skillsError.classList.remove('hidden');
    } else {
      skillsError.classList.add('hidden');
    }
  });
});
