// Main JavaScript file/**
 * LeadFlow Custom JavaScript
 * Dependencies: Bootstrap 5 (loaded in footer)
 */

'use strict';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {

    // ===== 1. AUTO-DISMISS FLASH MESSAGES =====
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            // Use Bootstrap's alert close method if available
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            } else {
                // Fallback: manually remove
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    });

    // ===== 2. CONFIRM DELETE ACTIONS =====
    const deleteButtons = document.querySelectorAll('.delete-btn, [data-confirm]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ===== 3. INITIALISE BOOTSTRAP TOOLTIPS =====
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    // ===== 4. DYNAMIC TABLE ROW LABELS FOR MOBILE (responsive tables) =====
    // If you're using the <td data-label="..."> approach from CSS, this isn't strictly needed,
    // but we can auto-populate data-label from the table headers if missing.
    const tables = document.querySelectorAll('.table');
    tables.forEach(function(table) {
        const headers = [];
        const headerCells = table.querySelectorAll('thead th');
        headerCells.forEach(th => headers.push(th.textContent.trim()));

        if (headers.length > 0) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (!cell.hasAttribute('data-label') && headers[index]) {
                        cell.setAttribute('data-label', headers[index]);
                    }
                });
            });
        }
    });

    // ===== 5. FORM VALIDATION (example for add/edit lead forms) =====
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // ===== 6. CONVERT STATUS DROPDOWN TO BADGES (if you have static status indicators) =====
    // Optional: if you render status as text, you can wrap them in badge spans
    const statusElements = document.querySelectorAll('.lead-status');
    statusElements.forEach(el => {
        const status = el.textContent.trim().toLowerCase();
        let badgeClass = 'status-badge ';
        if (status === 'new') badgeClass += 'status-new';
        else if (status === 'contacted') badgeClass += 'status-contacted';
        else if (status === 'converted') badgeClass += 'status-converted';
        else badgeClass += 'bg-secondary text-white';

        // Replace content with badge span
        const badge = document.createElement('span');
        badge.className = badgeClass;
        badge.textContent = el.textContent;
        el.innerHTML = '';
        el.appendChild(badge);
    });

    // ===== 7. QUICK FILTER SEARCH (for lead tables) =====
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const table = document.querySelector('.table');
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let text = '';
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    text += cell.textContent.toLowerCase() + ' ';
                });
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

}); // End DOMContentLoaded

/**
 * Utility function to show a temporary message (if needed)
 */
function showToast(message, type = 'success') {
    // Create a simple toast container if not exists
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toast.show();

    // Remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}