// Dropdown toggler driven by data attributes:
// - Wrapper: data-dropdown
// - Toggle:  data-dropdown-toggle
// - Menu:    data-dropdown-menu
export default function initDropdown(root = document) {
    const dropdowns = Array.from(root.querySelectorAll('[data-dropdown]'));

    const closeAll = () => {
        dropdowns.forEach((dropdown) => dropdown.classList.remove('is-open'));
    };

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        if (!toggle || !menu) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = dropdown.classList.contains('is-open');
            closeAll();
            dropdown.classList.toggle('is-open', !isOpen);
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', closeAll);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
}
