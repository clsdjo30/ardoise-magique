/**
 * Restaurant Selection Modal for Menu Creation
 */
(function() {
    'use strict';

    console.log('[Restaurant Modal] Script loaded');

    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('restaurantSelectionModal');

        if (!modalElement) {
            console.log('[Restaurant Modal] Modal element not found - user probably has only 1 restaurant');
            return;
        }

        console.log('[Restaurant Modal] Modal found, initializing...');

        const modal = new bootstrap.Modal(modalElement);
        let pendingMenuUrl = null;

        // Intercept clicks on menu creation links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');

            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Check if it's a menu creation link
            if (href.includes('/daily-menu/new') || href.includes('/special-menu/new')) {
                console.log('[Restaurant Modal] Menu creation link clicked:', href);
                e.preventDefault();
                e.stopPropagation();
                pendingMenuUrl = href;
                modal.show();
                console.log('[Restaurant Modal] Modal opened');
            }
        }, true); // Use capture phase to intercept early

        // Handle restaurant selection
        const restaurantChoices = document.querySelectorAll('.restaurant-choice');
        console.log('[Restaurant Modal] Found', restaurantChoices.length, 'restaurant choices');

        restaurantChoices.forEach(button => {
            button.addEventListener('click', function() {
                const restaurantId = this.dataset.restaurantId;
                const restaurantName = this.dataset.restaurantName;

                console.log('[Restaurant Modal] Restaurant selected:', restaurantName, '(ID:', restaurantId, ')');

                // Store selected restaurant in session
                fetch('/admin/api/set-restaurant', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ restaurantId: restaurantId })
                })
                .then(response => {
                    console.log('[Restaurant Modal] Response status:', response.status);
                    console.log('[Restaurant Modal] Response headers:', response.headers.get('content-type'));

                    // Clone the response to read it twice
                    return response.clone().text().then(text => {
                        console.log('[Restaurant Modal] Response text (first 500 chars):', text.substring(0, 500));
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('[Restaurant Modal] JSON parse error:', e);
                            console.error('[Restaurant Modal] Full response:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(data => {
                    console.log('[Restaurant Modal] Response data:', data);
                    if (data.success) {
                        console.log('[Restaurant Modal] Redirecting to:', pendingMenuUrl);
                        window.location.href = pendingMenuUrl;
                    } else {
                        console.error('[Restaurant Modal] Error:', data.error);
                        alert('Erreur: ' + (data.error || 'Impossible de sélectionner le restaurant'));
                    }
                })
                .catch(error => {
                    console.error('[Restaurant Modal] Fetch error:', error);
                    alert('Erreur de connexion au serveur: ' + error.message);
                });
            });
        });
    });
})();
