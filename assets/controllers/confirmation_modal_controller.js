import { Controller } from '@hotwired/stimulus';

/**
 * Controller for confirmation modal
 * Manages the display and user interaction with a custom confirmation modal
 * to replace native browser confirm() dialogs
 */
export default class extends Controller {
    static targets = ['modal', 'backdrop'];
    static values = {
        cancelUrl: String
    };

    /**
     * Show the confirmation modal
     */
    show(event) {
        event.preventDefault();
        console.log('[ConfirmationModal] Opening modal');

        if (this.hasModalTarget) {
            this.modalTarget.classList.add('active');
        }

        if (this.hasBackdropTarget) {
            this.backdropTarget.classList.add('active');
        }

        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    }

    /**
     * Hide the confirmation modal
     */
    hide(event) {
        if (event) {
            event.preventDefault();
        }

        console.log('[ConfirmationModal] Closing modal');

        if (this.hasModalTarget) {
            this.modalTarget.classList.remove('active');
        }

        if (this.hasBackdropTarget) {
            this.backdropTarget.classList.remove('active');
        }

        // Restore body scroll
        document.body.style.overflow = '';
    }

    /**
     * Confirm action - redirect to cancel URL
     */
    confirm(event) {
        event.preventDefault();
        console.log('[ConfirmationModal] User confirmed - redirecting to:', this.cancelUrlValue);

        if (this.cancelUrlValue) {
            window.location.href = this.cancelUrlValue;
        }
    }

    /**
     * Close modal when clicking outside (on backdrop)
     */
    closeOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            this.hide(event);
        }
    }

    /**
     * Handle escape key to close modal
     */
    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.hide();
        }
    }

    connect() {
        console.log('[ConfirmationModal] Controller connected');

        // Add keyboard listener
        this.boundHandleKeydown = this.handleKeydown.bind(this);
        document.addEventListener('keydown', this.boundHandleKeydown);
    }

    disconnect() {
        console.log('[ConfirmationModal] Controller disconnected');

        // Remove keyboard listener
        document.removeEventListener('keydown', this.boundHandleKeydown);

        // Restore body scroll
        document.body.style.overflow = '';
    }
}
