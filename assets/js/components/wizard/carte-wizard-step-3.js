/**
 * Carte Wizard Step 3 - Dish Selection Enhancement
 *
 * This module provides additional enhancements for the Step 3 of the carte wizard.
 * The main logic is handled by the dish_selector_controller.js Stimulus controller.
 *
 * This file is only loaded when the wizard step 3 is present on the page.
 */

export default function initCarteWizardStep3() {
    const wizardStep3 = document.querySelector('.wizard-step-3');

    if (!wizardStep3) {
        return; // Not on step 3, exit early
    }

    console.log('Carte Wizard Step 3 initialized');

    // Add smooth scroll behavior when navigating between sections
    const sections = wizardStep3.querySelectorAll('.section-dish-selector');

    // Add keyboard navigation for checkboxes
    enhanceKeyboardNavigation(wizardStep3);

    // Add visual feedback for form submission
    enhanceFormSubmission(wizardStep3);
}

/**
 * Enhance keyboard navigation for better accessibility
 */
function enhanceKeyboardNavigation(container) {
    container.addEventListener('keydown', (event) => {
        // Allow space key to toggle checkboxes on label click
        if (event.key === ' ' && event.target.classList.contains('variant-label')) {
            event.preventDefault();
            const checkbox = event.target.previousElementSibling;
            if (checkbox && checkbox.type === 'checkbox') {
                checkbox.click();
            }
        }
    });
}

/**
 * Add visual feedback during form submission
 */
function enhanceFormSubmission(container) {
    const form = container;

    form.addEventListener('submit', (event) => {
        // Add loading state to submit button
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Chargement...';

            // Reset button if form submission fails
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }, 5000);
        }
    });
}

/**
 * Initialize the step 3 enhancements when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarteWizardStep3);
} else {
    initCarteWizardStep3();
}
