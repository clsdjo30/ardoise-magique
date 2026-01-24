/**
 * Delete Confirmation Modal - Vanilla JS for EasyAdmin
 * Manages confirmation before deleting entities
 */
(function() {
    'use strict';

    let modal = null;
    let backdrop = null;
    let pendingForm = null;

    function init() {
        modal = document.querySelector('.confirmation-modal');
        backdrop = document.querySelector('.modal-backdrop');

        if (!modal || !backdrop) {
            console.warn('[DeleteConfirmation] Modal elements not found');
            return;
        }

        // Bind delete buttons
        document.querySelectorAll('[data-delete-confirm]').forEach(button => {
            button.addEventListener('click', handleDeleteClick);
        });

        // Bind modal actions
        document.querySelectorAll('[data-action="cancel"]').forEach(btn => {
            btn.addEventListener('click', hide);
        });

        document.querySelectorAll('[data-action="confirm"]').forEach(btn => {
            btn.addEventListener('click', confirm);
        });

        // Close on backdrop click
        backdrop.addEventListener('click', hide);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                hide();
            }
        });

        console.log('[DeleteConfirmation] Initialized');
    }

    function handleDeleteClick(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const form = button.closest('form');

        if (!form) {
            console.error('[DeleteConfirmation] No form found');
            return;
        }

        pendingForm = form;

        // Update item name if available
        const itemName = button.dataset.itemName || 'cet élément';
        const itemNameEl = document.querySelector('[data-item-name]');
        if (itemNameEl) {
            itemNameEl.textContent = itemName;
        }

        show();
    }

    function show() {
        if (modal) modal.classList.add('active');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
        console.log('[DeleteConfirmation] Modal opened');
    }

    function hide(event) {
        if (event) event.preventDefault();
        if (modal) modal.classList.remove('active');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
        pendingForm = null;
        console.log('[DeleteConfirmation] Modal closed');
    }

    function confirm(event) {
        event.preventDefault();
        if (pendingForm) {
            console.log('[DeleteConfirmation] Submitting form');
            pendingForm.submit();
        }
        hide();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
