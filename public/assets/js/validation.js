/**
 * Client-side form enhancements.
 * Adds lightweight validation for registration and project forms, disables duplicate submissions, and auto-hides alerts.
 */

document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.querySelector('#register-form');
    const projectForm = document.querySelector('#project-form');

    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const password = document.querySelector('#password').value.trim();
            const confirmPassword = document.querySelector('#confirm_password').value.trim();

            if (password.length < 8) {
                alert('Password must be at least 8 characters long.');
                event.preventDefault();
                return;
            }

            if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                alert('Password must include at least one uppercase letter and one number.');
                event.preventDefault();
                return;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                event.preventDefault();
            }
        });
    }

    if (projectForm) {
        projectForm.addEventListener('submit', function (event) {
            const title = document.querySelector('#title').value.trim();
            const startDate = document.querySelector('#start_date').value.trim();
            const endDate = document.querySelector('#end_date').value.trim();
            const description = document.querySelector('#short_description').value.trim();

            if (title.length < 3) {
                alert('Project title must be at least 3 characters.');
                event.preventDefault();
                return;
            }

            if (!startDate) {
                alert('Start date is required.');
                event.preventDefault();
                return;
            }

            if (endDate && endDate < startDate) {
                alert('End date cannot be earlier than start date.');
                event.preventDefault();
                return;
            }

            if (description.length < 10) {
                alert('Description must be at least 10 characters.');
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const submitButton = form.querySelector('button[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerText;
                submitButton.innerText = 'Processing...';
                submitButton.classList.add('is-loading');
            }
        });
    });

    const alerts = document.querySelectorAll('.alert');

    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.classList.add('alert-hide');
            });
        }, 3500);
    }
});