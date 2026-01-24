import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sectionsList', 'section', 'itemsList'];

    connect() {
        console.log('Carte Preview Controller connected');

        // Charger SortableJS si pas déjà chargé
        this.loadSortableJS().then(() => {
            this.initializeDragAndDrop();
        });
    }

    /**
     * Charge SortableJS depuis le CDN si nécessaire
     */
    loadSortableJS() {
        return new Promise((resolve) => {
            if (typeof Sortable !== 'undefined') {
                resolve();
            } else {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
                script.onload = () => {
                    console.log('SortableJS chargé depuis CDN');
                    resolve();
                };
                script.onerror = () => {
                    console.error('Erreur lors du chargement de SortableJS');
                    resolve(); // On continue même si le chargement échoue
                };
                document.head.appendChild(script);
            }
        });
    }

    /**
     * Initialise le drag & drop pour les sections et les items
     */
    initializeDragAndDrop() {
        if (typeof Sortable === 'undefined') {
            console.error('SortableJS n\'est pas disponible');
            return;
        }

        // Initialiser le drag & drop pour les sections
        this.initSectionsDragAndDrop();

        // Initialiser le drag & drop pour les items dans chaque section
        this.initItemsDragAndDrop();
    }

    /**
     * Initialise le drag & drop pour la liste des sections
     */
    initSectionsDragAndDrop() {
        if (!this.hasSectionsListTarget) {
            console.log('Pas de liste de sections');
            return;
        }

        const sectionsList = this.sectionsListTarget;

        // Éviter de réinitialiser si déjà présent
        if (sectionsList.sortableInstance) {
            return;
        }

        console.log('Initialisation du drag & drop pour les sections');

        const sortable = Sortable.create(sectionsList, {
            animation: 150,
            handle: '.section-drag-handle',
            draggable: '.preview-section',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',

            onEnd: (evt) => {
                console.log('Section déplacée de', evt.oldIndex, 'vers', evt.newIndex);
                this.updateSectionsPositions();
            }
        });

        sectionsList.sortableInstance = sortable;
        console.log('✓ Drag & drop activé pour les sections');
    }

    /**
     * Initialise le drag & drop pour les items dans toutes les sections
     */
    initItemsDragAndDrop() {
        const sections = this.sectionTargets;

        sections.forEach((section, sectionIndex) => {
            const itemsContainer = section.querySelector('.items-container');

            if (!itemsContainer) {
                return;
            }

            // Vérifier s'il y a des items
            const items = itemsContainer.querySelectorAll('.preview-item');
            if (items.length === 0) {
                return;
            }

            // Éviter de réinitialiser si déjà présent
            if (itemsContainer.sortableInstance) {
                return;
            }

            console.log(`Initialisation du drag & drop pour les items de la section ${sectionIndex}`);

            const sortable = Sortable.create(itemsContainer, {
                animation: 150,
                handle: '.item-drag-handle',
                draggable: '.preview-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',

                onEnd: (evt) => {
                    console.log(`Item déplacé de ${evt.oldIndex} vers ${evt.newIndex} dans la section ${sectionIndex}`);
                    this.updateItemsPositions(section);
                }
            });

            itemsContainer.sortableInstance = sortable;
        });

        console.log('✓ Drag & drop activé pour les items');
    }

    /**
     * Met à jour les positions des sections après un drag & drop
     */
    updateSectionsPositions() {
        const sections = this.sectionTargets;

        sections.forEach((section, newPosition) => {
            // Mettre à jour uniquement la valeur du champ position (pas le name!)
            const positionInput = section.querySelector('.section-position');
            if (positionInput) {
                positionInput.value = newPosition;
            }

            console.log(`Section mise à jour avec nouvelle position: ${newPosition}`);
        });
    }

    /**
     * Met à jour les positions des items dans une section après un drag & drop
     */
    updateItemsPositions(section) {
        const items = section.querySelectorAll('.preview-item');

        items.forEach((item, newPosition) => {
            // Mettre à jour uniquement la valeur du champ position (pas le name!)
            const positionInput = item.querySelector('.item-position');
            if (positionInput) {
                positionInput.value = newPosition;
            }

            console.log(`Item mis à jour avec nouvelle position: ${newPosition}`);
        });
    }

    /**
     * Déplier toutes les sections
     */
    expandAll() {
        const sections = this.sectionTargets;

        sections.forEach(section => {
            const toggleButton = section.querySelector('.toggle-items');
            const itemsContainer = section.querySelector('.items-container');

            if (toggleButton && itemsContainer) {
                toggleButton.setAttribute('aria-expanded', 'true');
                itemsContainer.classList.remove('collapsed');
            }
        });
    }

    /**
     * Replier toutes les sections
     */
    collapseAll() {
        const sections = this.sectionTargets;

        sections.forEach(section => {
            const toggleButton = section.querySelector('.toggle-items');
            const itemsContainer = section.querySelector('.items-container');

            if (toggleButton && itemsContainer) {
                toggleButton.setAttribute('aria-expanded', 'false');
                itemsContainer.classList.add('collapsed');
            }
        });
    }

    /**
     * Toggle l'affichage des items d'une section
     */
    toggleItems(event) {
        const button = event.currentTarget;
        const section = button.closest('.preview-section');
        const itemsContainer = section.querySelector('.items-container');

        if (!itemsContainer) return;

        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        button.setAttribute('aria-expanded', !isExpanded);

        if (isExpanded) {
            itemsContainer.classList.add('collapsed');
        } else {
            itemsContainer.classList.remove('collapsed');
        }
    }

    disconnect() {
        // Nettoyer les instances Sortable
        if (this.hasSectionsListTarget && this.sectionsListTarget.sortableInstance) {
            this.sectionsListTarget.sortableInstance.destroy();
        }

        const sections = this.sectionTargets;
        sections.forEach(section => {
            const itemsContainer = section.querySelector('.items-container');
            if (itemsContainer && itemsContainer.sortableInstance) {
                itemsContainer.sortableInstance.destroy();
            }
        });
    }
}
