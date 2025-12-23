import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['list', 'item'];

    connect() {
        this.index = this.itemTargets.length;
        console.log(`Collection connect: found ${this.itemTargets.length} items, setting index to ${this.index}`);
        this.updateSectionNumbers();
        this.updatePositions();
    }

    addItem(event) {
        event.preventDefault();

        const prototype = this.element.dataset.collectionPrototype;
        console.log(`addItem: adding item with index ${this.index}`);
        const newItem = prototype.replace(/__section__/g, this.index);

        // Create a temporary container to parse the HTML
        const temp = document.createElement('div');
        temp.innerHTML = newItem;

        // Get the li element
        const li = temp.querySelector('li');
        if (li) {
            li.dataset.collectionTarget = 'item';
            this.listTarget.appendChild(li);
            this.index++;
            console.log(`addItem: item added, new index is ${this.index}`);
            this.updateSectionNumbers();
            this.updatePositions();
        }
    }

    removeItem(event) {
        event.preventDefault();
        const item = event.target.closest('[data-collection-target="item"]');
        if (item) {
            item.remove();
            this.updateSectionNumbers();
            this.updatePositions();
        }
    }

    updateSectionNumbers() {
        this.itemTargets.forEach((item, index) => {
            const numberSpan = item.querySelector('.section-number');
            if (numberSpan) {
                numberSpan.textContent = `Section ${index + 1}`;
            }
        });
    }

    updatePositions() {
        console.log(`updatePositions: setting positions for ${this.itemTargets.length} items`);
        this.itemTargets.forEach((item, index) => {
            const positionInput = item.querySelector('.section-position');
            if (positionInput) {
                console.log(`  - Item ${index}: setting position to ${index}, field name: ${positionInput.name}`);
                positionInput.value = index;
            } else {
                console.warn(`  - Item ${index}: NO POSITION INPUT FOUND`);
            }
        });
    }
}
