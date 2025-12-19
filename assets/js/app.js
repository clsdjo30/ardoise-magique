import '../styles/public/app.scss';

import initModal from './components/modal.js';
import initDropdown from './components/dropdown.js';
import initTabs from './components/tabs.js';
import createToast from './components/toast.js';
import SectionProlongement from './components/section-prolongement.js';
import initNavbar from './components/navbar.js';
import initHero from './hero.js';

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initModal();
    initDropdown();
    initTabs();
    initHero();


    // Expose a simple toast helper for ad-hoc notifications in templates.
    window.showToast = createToast();

    // Initialize section prolongement animations
    new SectionProlongement();

});
