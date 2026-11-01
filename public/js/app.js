(function () {
    'use strict';

    var CONFIRM_TITLE = 'Confirm Deletion';
    var CONFIRM_MESSAGE = 'Are you sure you want to delete this record? This action cannot be undone.';
    var overlay = null;
    var cancelButton = null;
    var confirmButton = null;
    var activeForm = null;
    var activeSubmitter = null;
    var lastFocusedElement = null;
    var previousBodyOverflow = '';

    function injectStyles() {
        if (document.getElementById('app-confirm-styles')) return;

        var style = document.createElement('style');
        style.id = 'app-confirm-styles';
        style.textContent = [
            '.app-confirm-overlay,',
            '.app-confirm-overlay * { box-sizing: border-box; }',
            '.app-confirm-overlay {',
            '    position: fixed;',
            '    inset: 0;',
            '    z-index: 9999;',
            '    display: flex;',
            '    align-items: center;',
            '    justify-content: center;',
            '    padding: 20px;',
            '    background: rgba(15, 23, 42, 0.55);',
            '    opacity: 0;',
            '    visibility: hidden;',
            '    pointer-events: none;',
            '    transition: opacity 0.2s ease, visibility 0.2s ease;',
            '}',
            '.app-confirm-overlay.is-open {',
            '    opacity: 1;',
            '    visibility: visible;',
            '    pointer-events: auto;',
            '}',
            '.app-confirm-dialog {',
            '    width: min(420px, 100%);',
            '    padding: 28px 26px 24px;',
            '    border-radius: 16px;',
            '    background: #ffffff;',
            '    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.28);',
            '    text-align: center;',
            '    transform: translateY(12px) scale(0.97);',
            '    transition: transform 0.2s ease;',
            '}',
            '.app-confirm-overlay.is-open .app-confirm-dialog {',
            '    transform: translateY(0) scale(1);',
            '}',
            '.app-confirm-icon {',
            '    width: 56px;',
            '    height: 56px;',
            '    display: flex;',
            '    align-items: center;',
            '    justify-content: center;',
            '    margin: 0 auto 18px;',
            '    border-radius: 50%;',
            '    background: #fef2f2;',
            '    color: #dc2626;',
            '}',
            '.app-confirm-icon svg {',
            '    display: block;',
            '    width: 28px;',
            '    height: 28px;',
            '}',
            '.app-confirm-dialog h2 {',
            '    margin: 0 0 10px;',
            '    color: #172033;',
            '    font-size: 20px;',
            '    font-weight: 700;',
            '    line-height: 1.35;',
            '}',
            '.app-confirm-dialog p {',
            '    margin: 0;',
            '    color: #5b6678;',
            '    font-size: 14px;',
            '    line-height: 1.65;',
            '}',
            '.app-confirm-actions {',
            '    display: flex;',
            '    justify-content: center;',
            '    flex-wrap: wrap;',
            '    gap: 10px;',
            '    margin-top: 24px;',
            '}',
            '.app-confirm-btn {',
            '    min-height: 42px;',
            '    padding: 10px 18px;',
            '    border-radius: 9px;',
            '    font-family: inherit;',
            '    font-size: 14px;',
            '    font-weight: 700;',
            '    line-height: 1.4;',
            '    cursor: pointer;',
            '    transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;',
            '}',
            '.app-confirm-btn:focus-visible {',
            '    outline: 3px solid rgba(37, 99, 235, 0.3);',
            '    outline-offset: 2px;',
            '}',
            '.app-confirm-btn:disabled {',
            '    cursor: wait;',
            '    opacity: 0.7;',
            '}',
            '.app-confirm-cancel {',
            '    border: 1px solid #d7deea;',
            '    background: #ffffff;',
            '    color: #334155;',
            '}',
            '.app-confirm-cancel:hover {',
            '    background: #f1f5f9;',
            '}',
            '.app-confirm-delete {',
            '    border: 1px solid #dc2626;',
            '    background: #dc2626;',
            '    color: #ffffff;',
            '}',
            '.app-confirm-delete:hover {',
            '    border-color: #b91c1c;',
            '    background: #b91c1c;',
            '}',
            '@media (max-width: 480px) {',
            '    .app-confirm-overlay { padding: 14px; }',
            '    .app-confirm-dialog { padding: 24px 18px 20px; border-radius: 14px; }',
            '    .app-confirm-actions { flex-direction: column; }',
            '    .app-confirm-btn { width: 100%; }',
            '}',
            '@media (prefers-reduced-motion: reduce) {',
            '    .app-confirm-overlay,',
            '    .app-confirm-dialog { transition: none; }',
            '}'
        ].join('\n');

        document.head.appendChild(style);
    }

    function buildDialog() {
        if (overlay) return;

        injectStyles();

        overlay = document.createElement('div');
        overlay.className = 'app-confirm-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = '<div class="app-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message">' +
            '<div class="app-confirm-icon" aria-hidden="true">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>' +
            '<line x1="12" y1="9" x2="12" y2="13"></line>' +
            '<line x1="12" y1="17" x2="12.01" y2="17"></line>' +
            '</svg>' +
            '</div>' +
            '<h2 id="app-confirm-title">' + CONFIRM_TITLE + '</h2>' +
            '<p id="app-confirm-message">' + CONFIRM_MESSAGE + '</p>' +
            '<div class="app-confirm-actions">' +
            '<button type="button" class="app-confirm-btn app-confirm-cancel">Cancel</button>' +
            '<button type="button" class="app-confirm-btn app-confirm-delete">Yes, Delete</button>' +
            '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        cancelButton = overlay.querySelector('.app-confirm-cancel');
        confirmButton = overlay.querySelector('.app-confirm-delete');

        cancelButton.addEventListener('click', closeDialog);
        confirmButton.addEventListener('click', confirmDelete);
        overlay.addEventListener('mousedown', function (event) {
            if (event.target === overlay) {
                closeDialog();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (!overlay.classList.contains('is-open')) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                closeDialog();
                return;
            }

            if (event.key === 'Tab') {
                if (event.shiftKey && document.activeElement === cancelButton) {
                    event.preventDefault();
                    confirmButton.focus();
                } else if (!event.shiftKey && document.activeElement === confirmButton) {
                    event.preventDefault();
                    cancelButton.focus();
                }
            }
        });
    }

    function isDeleteForm(form) {
        if (!form || form.tagName !== 'FORM') return false;

        if (form.hasAttribute('data-confirm')) return true;

        if ((form.getAttribute('method') || 'GET').toUpperCase() === 'DELETE') return true;

        var methodInput = form.querySelector('input[name="_method"]');

        return !!methodInput && String(methodInput.value || '').toUpperCase() === 'DELETE';
    }

    function resolveDeleteForm(submitter, form) {
        if (form && isDeleteForm(form)) return form;

        if (!submitter) return null;

        var submitterForm = submitter.form || (submitter.closest ? submitter.closest('form') : null);

        if (!submitterForm) return null;

        if (isDeleteForm(submitterForm) || submitter.hasAttribute('data-confirm')) {
            return submitterForm;
        }

        return null;
    }

    function openDialog(form, submitter) {
        buildDialog();

        activeForm = form;
        activeSubmitter = submitter || null;
        lastFocusedElement = submitter || document.activeElement;
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        confirmButton.disabled = false;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');

        window.setTimeout(function () {
            if (overlay.classList.contains('is-open')) {
                cancelButton.focus();
            }
        }, 20);
    }

    function closeDialog() {
        if (!overlay || !overlay.classList.contains('is-open')) return;

        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousBodyOverflow;
        activeForm = null;
        activeSubmitter = null;

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }

        lastFocusedElement = null;
    }

    function confirmDelete() {
        if (!activeForm) return;

        var form = activeForm;
        var submitter = activeSubmitter;

        confirmButton.disabled = true;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousBodyOverflow;
        activeForm = null;
        activeSubmitter = null;

        form.setAttribute('data-delete-confirmed', 'true');
        form.setAttribute('data-delete-submitting', 'true');

        if (typeof form.requestSubmit === 'function') {
            try {
                form.requestSubmit(submitter || undefined);
            } catch (error) {
                form.submit();
            }
        } else {
            form.submit();
        }

        if (submitter && 'disabled' in submitter) {
            submitter.disabled = true;
        }
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM') return;

        if (form.getAttribute('data-delete-confirmed') === 'true') {
            form.removeAttribute('data-delete-confirmed');
            return;
        }

        if (form.getAttribute('data-delete-submitting') === 'true') {
            event.preventDefault();
            return;
        }

        var submitter = event.submitter || null;
        var deleteForm = resolveDeleteForm(submitter, form);

        if (!deleteForm) return;

        event.preventDefault();
        openDialog(deleteForm, submitter);
    });

    document.addEventListener('DOMContentLoaded', function () {
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
