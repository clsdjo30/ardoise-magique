// Tabs driven by data attributes:
// - Container: data-tabs
// - Tab button: data-tab-target="#panel-id"
// - Panel: id="panel-id" data-tab-panel
export default function initTabs(root = document) {
    const containers = Array.from(root.querySelectorAll('[data-tabs]'));

    containers.forEach((container) => {
        const tabButtons = Array.from(container.querySelectorAll('[data-tab-target]'));
        const panels = Array.from(container.querySelectorAll('[data-tab-panel]'));
        if (!tabButtons.length || !panels.length) {
            return;
        }

        const panelById = new Map(panels.map((panel) => [`#${panel.id}`, panel]));

        const activate = (targetId) => {
            tabButtons.forEach((btn) => {
                const isActive = btn.getAttribute('data-tab-target') === targetId;
                btn.setAttribute('aria-selected', String(isActive));
                btn.toggleAttribute('data-active', isActive);
            });

            panels.forEach((panel) => {
                const isActive = `#${panel.id}` === targetId;
                panel.hidden = !isActive;
            });
        };

        tabButtons.forEach((btn, index) => {
            if (index === 0) {
                activate(btn.getAttribute('data-tab-target'));
            }

            btn.addEventListener('click', (event) => {
                event.preventDefault();
                const target = btn.getAttribute('data-tab-target');
                if (!panelById.has(target)) {
                    return;
                }
                activate(target);
            });
        });
    });
}
