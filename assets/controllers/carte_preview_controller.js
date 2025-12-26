import { Controller } from '@hotwired/stimulus';

/**
 * Controller for drag-and-drop functionality in Carte Preview (Step 4)
 * Manages:
 * - Section reordering
 * - Item reordering within sections
 * - Collapsible sections
 */
export default class extends Controller {
    static targets = ['sectionsList', 'section', 'itemsList'];

    connect() {
        console.log('[CartePreview] Controller connected');
        this.loadSortableJS().then(() => {
            this.initializeSectionsSortable();
            this.initializeItemsSortables();
        }).catch(error => {
            console.error('[CartePreview] Failed to load SortableJS:', error);
        });
    }

    /**
     * Load SortableJS from CDN if not already loaded
     */
    loadSortableJS() {
        return new Promise((resolve, reject) => {
            if (typeof Sortable !== 'undefined') {
                console.log('[CartePreview] SortableJS already loaded');
                resolve();
                return;
            }

            console.log('[CartePreview] Loading SortableJS from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
            script.onload = () => {
                console.log('[CartePreview] SortableJS loaded successfully');
                resolve();
            };
            script.onerror = () => {
                reject(new Error('Failed to load SortableJS'));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Initialize drag-and-drop for sections
     */
    initializeSectionsSortable() {
        if (!this.hasSectionsListTarget) {
            console.warn('[CartePreview] No sections list found');
            return;
        }

        console.log('[CartePreview] Initializing sections sortable');
        Sortable.create(this.sectionsListTarget, {
            animation: 150,
            handle: '.section-drag-handle',
            draggable: '.preview-section',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: () => {
                this.updateSectionPositions();
            }
        });
    }

    /**
     * Initialize drag-and-drop for items in all sections
     */
    initializeItemsSortables() {
        if (!this.hasItemsListTarget) {
            console.warn('[CartePreview] No items lists found');
            return;
        }

        console.log(`[CartePreview] Initializing items sortable for ${this.itemsListTargets.length} sections`);
        this.itemsListTargets.forEach((itemsList, index) => {
            Sortable.create(itemsList, {
                animation: 150,
                handle: '.item-drag-handle',
                draggable: '.preview-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: () => {
                    this.updateItemPositions(index);
                }
            });
        });
    }

    /**
     * Update section position hidden inputs after drag
     */
    updateSectionPositions() {
        this.sectionTargets.forEach((section, index) => {
            const positionInput = section.querySelector('.section-position');
            if (positionInput) {
                positionInput.value = index;
                console.log(`[CartePreview] Section ${index} position updated`);
            }

            // Also update section index in all item inputs
            const sectionIndex = section.dataset.sectionIndex;
            section.dataset.sectionIndex = index;

            // Update all item inputs in this section
            const items = section.querySelectorAll('.preview-item');
            items.forEach((item, itemIndex) => {
                const inputs = item.querySelectorAll('input[name]');
                inputs.forEach(input => {
                    // Replace sections[oldIndex] with sections[newIndex]
                    input.name = input.name.replace(
                        `sections[${sectionIndex}]`,
                        `sections[${index}]`
                    );
                });
            });
        });
    }

    /**
     * Update item position hidden inputs after drag
     * @param {number} sectionIndex - Index of the section containing the items
     */
    updateItemPositions(sectionIndex) {
        const section = this.sectionTargets[sectionIndex];
        if (!section) return;

        const items = section.querySelectorAll('.preview-item');
        items.forEach((item, index) => {
            const positionInput = item.querySelector('.item-position');
            if (positionInput) {
                positionInput.value = index;
                console.log(`[CartePreview] Section ${sectionIndex}, Item ${index} position updated`);
            }

            // Update item index in input names
            const inputs = item.querySelectorAll('input[name*="[items]"]');
            inputs.forEach(input => {
                // Replace items][oldIndex] with items][newIndex]
                input.name = input.name.replace(
                    /\[items\]\[\d+\]/,
                    `[items][${index}]`
                );
            });
        });
    }

    /**
     * Toggle items visibility (collapse/expand)
     * @param {Event} event
     */
    toggleItems(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const section = button.closest('.preview-section');
        const itemsContainer = section.querySelector('.items-container');
        const icon = button.querySelector('.toggle-icon');

        if (itemsContainer.classList.contains('collapsed')) {
            itemsContainer.classList.remove('collapsed');
            icon.textContent = '▼';
            button.setAttribute('aria-expanded', 'true');
        } else {
            itemsContainer.classList.add('collapsed');
            icon.textContent = '▶';
            button.setAttribute('aria-expanded', 'false');
        }
    }

    /**
     * Expand all sections
     */
    expandAll() {
        const itemsContainers = this.element.querySelectorAll('.items-container');
        const toggleButtons = this.element.querySelectorAll('.toggle-items');

        itemsContainers.forEach(container => {
            container.classList.remove('collapsed');
        });

        toggleButtons.forEach(button => {
            const icon = button.querySelector('.toggle-icon');
            if (icon) icon.textContent = '▼';
            button.setAttribute('aria-expanded', 'true');
        });
    }

    /**
     * Collapse all sections
     */
    collapseAll() {
        const itemsContainers = this.element.querySelectorAll('.items-container');
        const toggleButtons = this.element.querySelectorAll('.toggle-items');

        itemsContainers.forEach(container => {
            container.classList.add('collapsed');
        });

        toggleButtons.forEach(button => {
            const icon = button.querySelector('.toggle-icon');
            if (icon) icon.textContent = '▶';
            button.setAttribute('aria-expanded', 'false');
        });
    }
}
