/**
 * Ardoise Digital - Category Selection Component
 * Handles interactive features for the category selection section
 */

export default function initArdoiseDigital() {
    const section = document.querySelector('.ardoise-digital');

    if (!section) {
        return;
    }

    const cards = section.querySelectorAll('.ardoise-digital__card');
    const readMoreBtn = section.querySelector('.ardoise-digital__button');

    // Add click handlers to category cards
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.dataset.category;
            handleCategoryClick(category, this);
        });

        // Add keyboard accessibility
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');

        card.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const category = this.dataset.category;
                handleCategoryClick(category, this);
            }
        });
    });

    // Handle Read More button
    if (readMoreBtn) {
        readMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleReadMore();
        });
    }

    // Intersection Observer for scroll animations
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');

                    // Stagger animation for cards
                    if (entry.target.classList.contains('ardoise-digital__card')) {
                        const index = Array.from(cards).indexOf(entry.target);
                        entry.target.style.animationDelay = `${index * 100}ms`;
                    }
                }
            });
        }, observerOptions);

        // Observe section elements
        observer.observe(section.querySelector('.ardoise-digital__header'));
        cards.forEach(card => observer.observe(card));
        if (readMoreBtn) {
            observer.observe(readMoreBtn.parentElement);
        }
    }

    // Add initial animation classes
    addAnimationClasses();
}

/**
 * Handle category card click
 * @param {string} category - The category identifier
 * @param {HTMLElement} cardElement - The clicked card element
 */
function handleCategoryClick(category, cardElement) {
    // Visual feedback
    cardElement.classList.add('is-selected');

    // Remove selection after animation
    setTimeout(() => {
        cardElement.classList.remove('is-selected');
    }, 300);

    // Log category selection (can be replaced with actual navigation or filtering logic)
    console.log(`Category selected: ${category}`);

    // You can add custom logic here, such as:
    // - Navigate to a category page
    // - Filter menu items
    // - Show a modal with category details
    // - Track analytics

    // Example: Show a toast notification
    if (window.showToast) {
        window.showToast(`You selected: ${getCategoryDisplayName(category)}`, 'info');
    }
}

/**
 * Handle Read More button click
 */
function handleReadMore() {
    console.log('Read More clicked');

    // You can add custom logic here, such as:
    // - Scroll to a specific section
    // - Navigate to a page with more information
    // - Show a modal with additional content

    // Example: Show a toast notification
    if (window.showToast) {
        window.showToast('Discover more about our menu categories!', 'info');
    }
}

/**
 * Get display name for a category
 * @param {string} category - The category identifier
 * @returns {string} - The display name
 */
function getCategoryDisplayName(category) {
    const names = {
        'chicken': 'Chicken Leg',
        'starter': 'Starter Dish',
        'appetizers': 'Appetizers Dish',
        'desserts': 'Desserts Dish',
        'drinks': 'Drinks'
    };

    return names[category] || category;
}

/**
 * Add animation classes for scroll effects
 */
function addAnimationClasses() {
    const style = document.createElement('style');
    style.textContent = `
        .ardoise-digital__header,
        .ardoise-digital__card,
        .ardoise-digital__cta {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .ardoise-digital__header.is-visible,
        .ardoise-digital__card.is-visible,
        .ardoise-digital__cta.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .ardoise-digital__card.is-selected {
            transform: scale(0.95) translateY(-5px);
        }
    `;
    document.head.appendChild(style);
}
