import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Load catalog for all sections on connect
        this.loadAllCatalogs();

        // Restore visual selected items from hidden inputs
        this.restoreSelectedItems();
    }

    async loadAllCatalogs() {
        const sections = this.element.querySelectorAll('.section-dish-selector');
        console.log(`Found ${sections.length} sections to load`);
        sections.forEach(section => {
            const sectionIndex = section.dataset.sectionIndex;
            const categoryId = section.dataset.categoryId;
            console.log(`Loading catalog for section ${sectionIndex}, category ${categoryId}`);
            this.loadCatalog(sectionIndex);
        });
    }

    restoreSelectedItems() {
        const selectedLists = this.element.querySelectorAll('.selected-list');
        selectedLists.forEach(async (selectedContainer) => {
            const sectionIndex = selectedContainer.dataset.sectionIndex;
            const hiddenInputs = selectedContainer.querySelectorAll('input[type="hidden"]');

            if (hiddenInputs.length > 0) {
                // Remove empty state
                const emptyState = selectedContainer.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }

                // Fetch variant details for all selected items
                const variantIds = Array.from(hiddenInputs).map(input => input.value);
                if (variantIds.length > 0) {
                    await this.fetchAndRenderSelectedVariants(sectionIndex, variantIds, selectedContainer);
                }
            }
        });
    }

    async fetchAndRenderSelectedVariants(sectionIndex, variantIds, selectedContainer) {
        try {
            const url = `/api/dish-catalog/variants?ids=${variantIds.join(',')}`;
            const response = await fetch(url, {
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (data.success) {
                data.variants.forEach((variantData) => {
                    this.renderSelectedItem(
                        sectionIndex,
                        variantData.id,
                        variantData.dishName,
                        variantData.label,
                        selectedContainer
                    );
                });
            }
        } catch (error) {
            console.error('Error fetching variant details:', error);
        }
    }

    renderSelectedItem(sectionIndex, variantId, dishName, variantLabel, selectedContainer) {
        // Check if visual item already exists
        const existingItem = selectedContainer.querySelector(`.selected-item[data-variant-id="${variantId}"]`);
        if (existingItem) {
            return;
        }

        // Add visual item
        const item = document.createElement('div');
        item.className = 'selected-item';
        item.dataset.variantId = variantId;
        item.innerHTML = `
            <div class="selected-item-info">
                <div class="selected-item-name">${this.escapeHtml(dishName)}</div>
                <div class="selected-item-variant">${this.escapeHtml(variantLabel)}</div>
            </div>
            <button type="button" class="selected-item-remove" data-action="click->dish-selector#removeVariant">
                Retirer
            </button>
        `;

        // Insert before hidden inputs
        const firstInput = selectedContainer.querySelector('input');
        if (firstInput) {
            selectedContainer.insertBefore(item, firstInput);
        } else {
            selectedContainer.appendChild(item);
        }
    }

    async loadCatalog(sectionIndex, query = '') {
        try {
            // Get the category ID for this section
            const sectionElement = this.element.querySelector(`[data-section-index="${sectionIndex}"]`);
            const categoryId = sectionElement ? sectionElement.dataset.categoryId : null;

            // Build URL with category filter
            let url = `/api/dish-catalog/search?q=${encodeURIComponent(query)}`;
            if (categoryId) {
                url += `&category=${categoryId}`;
            }

            console.log(`Loading catalog for section ${sectionIndex}, URL: ${url}`);

            const response = await fetch(url, {
                credentials: 'same-origin'
            });
            const data = await response.json();

            console.log(`Received ${data.count} dishes for section ${sectionIndex}`);
            console.log('Dish names:', data.dishes.map(d => d.name));

            if (data.success) {
                this.renderCatalog(sectionIndex, data.dishes);
            }
        } catch (error) {
            console.error('Error loading catalog:', error);
            this.showError(sectionIndex, 'Erreur lors du chargement du catalogue');
        }
    }

    renderCatalog(sectionIndex, dishes) {
        console.log(`renderCatalog called for section ${sectionIndex} with ${dishes.length} dishes`);
        const catalogContainer = this.element.querySelector(`[data-dish-selector-target="catalog-${sectionIndex}"]`);
        if (!catalogContainer) {
            console.warn(`renderCatalog: catalog container not found for section ${sectionIndex}`);
            return;
        }

        if (dishes.length === 0) {
            const sectionElement = this.element.querySelector(`[data-section-index="${sectionIndex}"]`);
            const categoryName = sectionElement ? sectionElement.querySelector('h3').textContent : 'cette catégorie';
            catalogContainer.innerHTML = `<p class="loading-text">Aucun plat trouvé pour "${categoryName}". Créez des plats de cette catégorie dans votre bibliothèque.</p>`;
            return;
        }

        console.log(`renderCatalog: replacing content for section ${sectionIndex}`);
        catalogContainer.innerHTML = dishes.map(dish => `
            <div class="dish-card">
                <div class="dish-card-name">${this.escapeHtml(dish.name)}</div>
                ${dish.description ? `<div class="dish-card-description">${this.escapeHtml(dish.description)}</div>` : ''}
                <div class="dish-card-variants">
                    ${dish.variants.map(variant => `
                        <span class="variant-badge"
                              data-action="click->dish-selector#selectVariant"
                              data-variant-id="${variant.id}"
                              data-dish-name="${this.escapeHtml(dish.name)}"
                              data-variant-label="${this.escapeHtml(variant.label)}"
                              data-section-index="${sectionIndex}">
                            ${this.escapeHtml(variant.label)} - ${variant.priceEuros.toFixed(2)}€
                        </span>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }

    selectVariant(event) {
        const variantId = event.target.dataset.variantId;
        const dishName = event.target.dataset.dishName;
        const variantLabel = event.target.dataset.variantLabel;
        const sectionIndex = event.target.dataset.sectionIndex;

        this.addToSelected(sectionIndex, variantId, dishName, variantLabel);
    }

    addToSelected(sectionIndex, variantId, dishName, variantLabel) {
        const selectedContainer = this.element.querySelector(`.selected-list[data-section-index="${sectionIndex}"]`);
        if (!selectedContainer) return;

        // Check if already selected
        const existingInput = selectedContainer.querySelector(`input[value="${variantId}"]`);
        if (existingInput) {
            this.showNotification('Ce plat est déjà sélectionné', 'warning');
            return;
        }

        // Remove empty state
        const emptyState = selectedContainer.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }

        // Add hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `selectedVariants[${sectionIndex}][]`;
        input.value = variantId;
        input.dataset.variantId = variantId;
        selectedContainer.appendChild(input);

        // Render visual item using shared method
        this.renderSelectedItem(sectionIndex, variantId, dishName, variantLabel, selectedContainer);
    }

    removeVariant(event) {
        const item = event.target.closest('.selected-item');
        if (!item) return;

        const variantId = item.dataset.variantId;
        const selectedContainer = item.closest('.selected-list');

        // Remove visual item
        item.remove();

        // Remove hidden input
        const input = selectedContainer.querySelector(`input[value="${variantId}"]`);
        if (input) {
            input.remove();
        }

        // Show empty state if no items left
        const remainingItems = selectedContainer.querySelectorAll('.selected-item');
        if (remainingItems.length === 0) {
            const emptyState = document.createElement('p');
            emptyState.className = 'empty-state';
            emptyState.textContent = 'Aucun plat sélectionné pour cette section';
            selectedContainer.appendChild(emptyState);
        }
    }

    async search(event) {
        const input = event.target;
        const sectionIndex = input.dataset.sectionIndex;
        const query = input.value;

        // Debounce search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.loadCatalog(sectionIndex, query);
        }, 300);
    }

    showError(sectionIndex, message) {
        const catalogContainer = this.element.querySelector(`[data-dish-selector-target="catalog-${sectionIndex}"]`);
        if (catalogContainer) {
            catalogContainer.innerHTML = `<p class="loading-text" style="color: #dc2626;">${message}</p>`;
        }
    }

    showNotification(message, type = 'info') {
        // Simple notification - can be enhanced with a toast library
        alert(message);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
