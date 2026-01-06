/**
 * Mobile Sidebar Close Handler
 * Allows closing the mobile sidebar by clicking outside or on close button
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('[Mobile Sidebar] Initializing close handler');

    /**
     * Close the mobile sidebar by removing the visibility class
     */
    function closeMobileSidebar() {
        const body = document.body;
        if (body.classList.contains('ea-mobile-sidebar-visible')) {
            console.log('[Mobile Sidebar] Closing sidebar');
            body.classList.remove('ea-mobile-sidebar-visible');

            // Also remove the modal-backdrop if it exists
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }

    /**
     * Listen for clicks to close sidebar when clicking outside
     * Using capture phase to handle before other listeners
     */
    function initOverlayListener() {
        document.addEventListener('click', function(event) {
            // Only proceed if mobile sidebar is visible
            if (!document.body.classList.contains('ea-mobile-sidebar-visible')) {
                return;
            }

            const sidebar = document.querySelector('.sidebar-wrapper');
            const responsiveHeader = document.querySelector('.responsive-header');

            // Don't close if clicking on sidebar
            if (sidebar && sidebar.contains(event.target)) {
                return;
            }

            // Don't close if clicking on responsive header (contains toggle button)
            if (responsiveHeader && responsiveHeader.contains(event.target)) {
                return;
            }

            // Don't close if clicking on any toggle button
            if (event.target.closest('[data-ea-toggle-sidebar]')) {
                return;
            }

            // Click was outside sidebar, close it
            console.log('[Mobile Sidebar] Click outside detected, closing');
            closeMobileSidebar();
        }, false); // Use bubbling phase, not capture
    }

    /**
     * Add a close button to the sidebar if it doesn't exist
     */
    function addCloseButton() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        // Check if close button already exists
        if (sidebar.querySelector('.mobile-sidebar-close')) {
            return;
        }

        // Create close button
        const closeButton = document.createElement('button');
        closeButton.className = 'mobile-sidebar-close';
        closeButton.innerHTML = '✕';
        closeButton.setAttribute('aria-label', 'Fermer le menu');
        closeButton.addEventListener('click', closeMobileSidebar);

        // Insert at the beginning of sidebar
        sidebar.insertBefore(closeButton, sidebar.firstChild);
        console.log('[Mobile Sidebar] Close button added');
    }

    // Initialize listener
    initOverlayListener();

    // Add close button on mobile
    if (window.innerWidth <= 991) {
        addCloseButton();
    }

    // Re-add close button when window is resized to mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 991) {
            addCloseButton();
        }
    });

    console.log('[Mobile Sidebar] Close handler initialized');
});
