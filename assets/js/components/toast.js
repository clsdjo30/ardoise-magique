// Lightweight toast helper. Usage:
// const showToast = createToast();
// showToast({ message: 'Saved', type: 'success', duration: 3200 });
export default function createToast(root = document.body) {
    let container = root.querySelector('[data-toast-container]');
    if (!container) {
        container = root.appendChild(document.createElement('div'));
        container.dataset.toastContainer = '';
        container.style.position = 'fixed';
        container.style.right = '1rem';
        container.style.bottom = '1rem';
        container.style.display = 'grid';
        container.style.gap = '0.5rem';
        container.style.zIndex = '60';
    }

    return ({ message, type = 'info', duration = 3000 }) => {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.dataset.toast = type;
        toast.style.padding = '0.75rem 1rem';
        toast.style.borderRadius = '10px';
        toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.12)';
        toast.style.color = '#2f1b0c';
        toast.style.background = '#fffaf6';
        toast.style.border = '1px solid #d4c4b7';

        const palette = {
            success: '#4a7a4a',
            warning: '#b2853f',
            info: '#6c7c90',
            danger: '#b85c38'
        };
        const tone = palette[type] || palette.info;
        toast.style.borderColor = tone;
        toast.style.color = '#2f1b0c';
        toast.style.background = '#f8f3ef';

        container.appendChild(toast);

        const timeout = setTimeout(() => {
            toast.remove();
        }, duration);

        toast.addEventListener('click', () => {
            clearTimeout(timeout);
            toast.remove();
        });
    };
}
