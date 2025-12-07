import '../stimulus_bootstrap.js';
import '../styles/app.css';

import initModal from './components/modal.js';
import initDropdown from './components/dropdown.js';
import initTabs from './components/tabs.js';
import createToast from './components/toast.js';

document.addEventListener('DOMContentLoaded', () => {
    initModal();
    initDropdown();
    initTabs();

    // Expose a simple toast helper for ad-hoc notifications in templates.
    window.showToast = createToast();
});
