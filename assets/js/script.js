/**
 * Student School Management System — Main JavaScript
 * 
 * Handles: sidebar toggle, modals, form validation,
 * search filtering, toasts, and interactive behaviors.
 */

document.addEventListener('DOMContentLoaded', () => {
    // ── Sidebar Toggle (Mobile) ──────────────────────────────
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('open');
        });

        // Close sidebar on overlay click
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            });
        }
    }

    // ── Modal Management ─────────────────────────────────────
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Focus first input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) setTimeout(() => firstInput.focus(), 100);
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });

    // ── Password Toggle ──────────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.innerHTML = isPassword
                    ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
                    : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
            }
        });
    });

    // ── Role Selector (Login Page) ───────────────────────────
    document.querySelectorAll('.role-option').forEach(option => {
        option.addEventListener('click', () => {
            document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
            option.classList.add('active');
            const radio = option.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // ── Table Search Filter ──────────────────────────────────
    const searchInputs = document.querySelectorAll('[data-search-table]');
    searchInputs.forEach(input => {
        input.addEventListener('input', () => {
            const tableId = input.getAttribute('data-search-table');
            const table = document.getElementById(tableId);
            if (!table) return;

            const query = input.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // ── Form Validation ──────────────────────────────────────
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(el => el.remove());
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Required fields
            form.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                    const error = document.createElement('div');
                    error.className = 'form-error';
                    error.textContent = 'This field is required';
                    field.parentElement.appendChild(error);
                }
            });

            // Email validation
            form.querySelectorAll('[type="email"]').forEach(field => {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    valid = false;
                    field.classList.add('is-invalid');
                    const error = document.createElement('div');
                    error.className = 'form-error';
                    error.textContent = 'Enter a valid email address';
                    field.parentElement.appendChild(error);
                }
            });

            // Grade validation (0-20)
            form.querySelectorAll('[data-grade]').forEach(field => {
                const val = parseFloat(field.value);
                if (isNaN(val) || val < 0 || val > 20) {
                    valid = false;
                    field.classList.add('is-invalid');
                    const error = document.createElement('div');
                    error.className = 'form-error';
                    error.textContent = 'Grade must be between 0 and 20';
                    field.parentElement.appendChild(error);
                }
            });

            if (!valid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) firstError.focus();
            }
        });
    });

    // ── Real-time Input Validation ───────────────────────────
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('blur', () => {
            if (input.required && !input.value.trim()) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
            } else if (input.value.trim()) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        });
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid');
        });
    });

    // ── Auto-dismiss Toast ───────────────────────────────────
    const toast = document.getElementById('flash-toast');
    if (toast) {
        setTimeout(() => {
            toast.style.animation = 'toastFadeOut 0.4s ease forwards';
            setTimeout(() => toast.remove(), 500);
        }, 5000);
    }

    // ── Confirm Delete ───────────────────────────────────────
    window.confirmDelete = function(message, formId) {
        if (confirm(message || 'Are you sure you want to delete this item?')) {
            const form = document.getElementById(formId);
            if (form) form.submit();
        }
    };

    // ── Print Transcript ─────────────────────────────────────
    window.printTranscript = function() {
        window.print();
    };
});
