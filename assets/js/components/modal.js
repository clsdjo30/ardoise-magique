// Basic modal controller driven by data attributes:
// - Trigger buttons: data-modal-target="#modal-id"
// - Modal container:  data-modal role="dialog" id="modal-id"
// - Close buttons:    data-modal-close
export default function initModal(root = document) {
    const modals = Array.from(root.querySelectorAll('[data-modal][id]'));
    const triggers = Array.from(root.querySelectorAll('[data-modal-target]'));

    const closeModal = (modal) => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    const openModal = (modal) => {
        modal.classList.add('is-open');
        modal.removeAttribute('aria-hidden');
    };

    const lookup = new Map(modals.map((modal) => [`#${modal.id}`, modal]));

    triggers.forEach((trigger) => {
        const targetSelector = trigger.getAttribute('data-modal-target');
        const modal = lookup.get(targetSelector);
        if (!modal) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(modal);
        });
    });

    modals.forEach((modal) => {
        const closeButtons = modal.querySelectorAll('[data-modal-close]');
        closeButtons.forEach((btn) =>
            btn.addEventListener('click', () => closeModal(modal))
        );

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            modals.forEach(closeModal);
        }
    });
}
