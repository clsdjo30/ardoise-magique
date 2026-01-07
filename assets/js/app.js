import '../stimulus_bootstrap.js';
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

    // Conditionally load carte wizard step 3 enhancements
    if (document.querySelector('.wizard-step-3')) {
        import('./components/wizard/carte-wizard-step-3.js')
            .then(module => {
                console.log('Carte Wizard Step 3 module loaded');
            })
            .catch(error => {
                console.error('Error loading Carte Wizard Step 3 module:', error);
            });
    }

});
