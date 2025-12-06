/**
 * Collection Field - Affichage dynamique du nom dans l'accordion header + Drag & Drop
 */

document.addEventListener('DOMContentLoaded', function() {
    // Charger SortableJS depuis CDN si pas déjà chargé
    if (typeof Sortable === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
        script.onload = function() {
            console.log('SortableJS chargé depuis CDN');
            initCollectionFields();
        };
        document.head.appendChild(script);
    } else {
        initCollectionFields();
    }
});

// Réinitialiser après les événements EasyAdmin
document.addEventListener('ea.collection.item-added', function(event) {
    console.log('Item added:', event.detail);
    setTimeout(() => {
        initCollectionFields();
    }, 100);
});

document.addEventListener('ea.collection.item-removed', function(event) {
    console.log('Item removed:', event.detail);
    // Pas besoin de réinitialiser, l'item est déjà supprimé du DOM
});

function initCollectionFields() {
    // Initialiser tous les champs de collection personnalisés
    const collections = document.querySelectorAll('.collection-custom');

    collections.forEach(collection => {
        // Initialiser les items existants
        updateAllItemTitles(collection);

        // Observer les changements dans les champs "nom"
        observeNameFields(collection);

        // Observer l'ajout de nouveaux items (fallback si événement EA ne fonctionne pas)
        observeNewItems(collection);

        // Initialiser le drag & drop
        initDragAndDrop(collection);

        // NE PAS toucher aux boutons de suppression - laisser EasyAdmin gérer complètement
        // observeDeleteButtons(collection); // COMMENTÉ
    });
}

function updateAllItemTitles(collection) {
    const items = collection.querySelectorAll('.field-collection-item');

    items.forEach(item => {
        updateItemTitle(item);
    });
}

function updateItemTitle(item) {
    // Trouver le bouton accordion
    const accordionButton = item.querySelector('.accordion-button');
    if (!accordionButton) {
        console.log('Pas de bouton accordion trouvé pour cet item', item);
        return;
    }

    // Chercher ou créer l'élément de titre
    let titleElement = accordionButton.querySelector('.item-title');
    if (!titleElement) {
        titleElement = document.createElement('span');
        titleElement.className = 'item-title';
        accordionButton.appendChild(titleElement);
        console.log('Élément titre créé et ajouté', titleElement);
    }

    // Chercher le champ "nom" (name, titre, etc.)
    const nameInput = item.querySelector('input[name*="[name]"]') ||
                      item.querySelector('input[name*="[titre]"]') ||
                      item.querySelector('input[type="text"]:first-of-type');

    console.log('Recherche du champ nom dans l\'item:', item);
    console.log('Champ nom trouvé:', nameInput);

    if (nameInput) {
        const value = nameInput.value.trim();
        titleElement.textContent = value || '';
        console.log('Titre mis à jour:', value || '(vide)');
    } else {
        titleElement.textContent = '';
        console.log('Aucun champ nom trouvé, titre vidé');
    }
}

function observeNameFields(collection) {
    // Observer les changements dans tous les champs "nom"
    const nameInputs = collection.querySelectorAll('input[name*="[name]"], input[name*="[titre]"]');

    nameInputs.forEach(input => {
        input.addEventListener('input', function() {
            const item = this.closest('.field-collection-item');
            if (item) {
                updateItemTitle(item);
            }
        });

        input.addEventListener('blur', function() {
            const item = this.closest('.field-collection-item');
            if (item) {
                updateItemTitle(item);
            }
        });
    });
}

function observeNewItems(collection) {
    // Observer l'ajout de nouveaux items dans la collection
    const container = collection.querySelector('[data-empty-collection]');
    if (!container) return;

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.classList.contains('field-collection-item')) {
                    // Nouvel item ajouté
                    console.log('MutationObserver: Nouvel item détecté', node);
                    updateItemTitle(node);

                    // Observer les champs de ce nouvel item
                    const nameInputs = node.querySelectorAll('input[name*="[name]"], input[name*="[titre]"]');
                    nameInputs.forEach(input => {
                        input.addEventListener('input', function() {
                            updateItemTitle(node);
                        });
                        input.addEventListener('blur', function() {
                            updateItemTitle(node);
                        });
                    });
                }
            });
        });
    });

    observer.observe(container, {
        childList: true,
        subtree: false
    });
}

// Fonction supprimée - on laisse EasyAdmin gérer complètement la suppression
// function observeDeleteButtons(collection) { ... }

/**
 * Initialise le drag & drop pour une collection
 */
function initDragAndDrop(collection) {
    // Vérifier que Sortable est disponible
    if (typeof Sortable === 'undefined') {
        console.error('[Drag&Drop] SortableJS n\'est pas chargé!');
        return;
    }

    // Chercher le conteneur qui contient directement les items
    // Structure: .ea-form-collection-items > div[data-empty-collection]
    const container = collection.querySelector('[data-empty-collection]');
    if (!container) {
        // Pas de conteneur = collection vide ou structure différente, on ignore silencieusement
        return;
    }

    // Vérifier s'il y a des items à drag
    const items = container.querySelectorAll('.field-collection-item');
    if (items.length === 0) {
        // Pas d'items = rien à drag pour le moment, on ignore silencieusement
        return;
    }

    // Éviter de réinitialiser Sortable si déjà présent
    if (container.sortableInstance) {
        return;
    }

    console.log('[Drag&Drop] Initialisation du drag & drop avec', items.length, 'items');
    const sortable = Sortable.create(container, {
        animation: 150,
        handle: '.accordion-button', // On peut drag par le header
        draggable: '.field-collection-item', // Les éléments draggables
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',

        // Callback après le drag
        onEnd: function(evt) {
            console.log('[Drag&Drop] Item déplacé de', evt.oldIndex, 'vers', evt.newIndex);
            updatePositions(collection);
        }
    });

    // Stocker l'instance pour éviter les réinitialisations
    container.sortableInstance = sortable;
    console.log('[Drag&Drop] ✓ Drag & drop activé!');
}

/**
 * Met à jour les champs position après un drag & drop
 */
function updatePositions(collection) {
    const items = collection.querySelectorAll('.field-collection-item');

    items.forEach((item, index) => {
        // 1. Mettre à jour le champ position
        let positionInput = item.querySelector('input[name*="[position]"]');

        // Si le champ position n'existe pas, on le crée en hidden
        if (!positionInput) {
            positionInput = document.createElement('input');
            positionInput.type = 'hidden';

            // Trouver le nom du champ parent pour construire le bon name
            const nameInput = item.querySelector('input[name*="[name]"]');
            if (nameInput) {
                const parentName = nameInput.name.replace('[name]', '[position]');
                positionInput.name = parentName;
            }

            // Ajouter le champ au formulaire
            const form = item.querySelector('.accordion-body');
            if (form) {
                form.appendChild(positionInput);
            }
        }

        // Mettre à jour la valeur
        positionInput.value = index;

        // 2. Mettre à jour les IDs et attributs des accordions pour Bootstrap
        updateAccordionIds(item, index);

        console.log('Position mise à jour:', positionInput.name, '=', index);
    });
}

/**
 * Met à jour les IDs des accordions après un drag & drop
 * pour que les boutons collapse fonctionnent correctement
 */
function updateAccordionIds(item, newIndex) {
    // Trouver le nom de la collection (entree, plat, dessert, items)
    const nameInput = item.querySelector('input[name*="[name]"]');
    if (!nameInput) return;

    // Extraire le nom de la collection depuis le name: Ardoise[entree][0][name]
    const match = nameInput.name.match(/Ardoise\[(\w+)\]/);
    if (!match) return;

    const collectionName = match[1]; // ex: "entree", "plat", "dessert", "items"
    const newId = `Ardoise_${collectionName}_${newIndex}`;

    // Mettre à jour le bouton accordion
    const accordionButton = item.querySelector('.accordion-button');
    if (accordionButton) {
        accordionButton.setAttribute('data-bs-target', `#${newId}-contents`);
        accordionButton.setAttribute('aria-controls', `${newId}-contents`);
    }

    // Mettre à jour le collapse div
    const collapseDiv = item.querySelector('.accordion-collapse');
    if (collapseDiv) {
        collapseDiv.id = `${newId}-contents`;
        collapseDiv.setAttribute('aria-labelledby', `${newId}-header`);
    }

    // Mettre à jour tous les champs du formulaire avec le nouvel index
    const allInputs = item.querySelectorAll('input, textarea, select');
    allInputs.forEach(input => {
        if (input.name && input.name.includes(`[${collectionName}]`)) {
            // Remplacer l'ancien index par le nouveau dans le name
            input.name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);

            // Mettre à jour l'ID si présent
            if (input.id && input.id.includes(`${collectionName}_`)) {
                input.id = input.id.replace(/_\d+_/, `_${newIndex}_`);
            }
        }
    });

    // Mettre à jour les labels correspondants
    const labels = item.querySelectorAll('label[for]');
    labels.forEach(label => {
        if (label.htmlFor && label.htmlFor.includes(`${collectionName}_`)) {
            label.htmlFor = label.htmlFor.replace(/_\d+_/, `_${newIndex}_`);
        }
    });
}
