// Navbar mobile toggle
// - Navbar wrapper: .c-navbar
// - Toggle button: .c-navbar__toggle
// - Mobile menu: .c-navbar__menu
export default function initNavbar(root = document) {
    const navbar = root.querySelector('.c-navbar');
    if (!navbar) return;

    const toggle = navbar.querySelector('.c-navbar__toggle');
    const menu = navbar.querySelector('.c-navbar__menu');
    const actions = navbar.querySelector('.c-navbar__actions');

    if (!toggle || !menu) return;

    const closeMenu = () => {
        navbar.classList.remove('is-open');
        toggle.setAttribute('aria-label', 'Ouvrir le menu');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const openMenu = () => {
        navbar.classList.add('is-open');
        toggle.setAttribute('aria-label', 'Fermer le menu');
        toggle.setAttribute('aria-expanded', 'true');
    };

    const toggleMenu = () => {
        if (navbar.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu();
    });

    // Close menu when clicking on a link
    const links = menu.querySelectorAll('.c-navbar__link');
    links.forEach(link => {
        link.addEventListener('click', () => {
            closeMenu();
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (event) => {
        if (!navbar.contains(event.target)) {
            closeMenu();
        }
    });

    // Close menu on escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    // Close menu when window is resized above mobile breakpoint
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 800) {
                closeMenu();
            }
        }, 250);
    });
}
