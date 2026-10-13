(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Confirm destructive actions (delete records, etc.).
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var message = form.getAttribute('data-confirm') || 'Are you sure?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });

        // Prevent accidental double submission of the confirm & submit step.
        document.querySelectorAll('form[data-single-submit]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Submitting…';
                }
            });
        });
    });
})();
