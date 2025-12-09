/**
 * Section Prolongement - Animation au scroll
 * Style: Bistrot Moderne
 *
 * Active les animations lorsque la section entre dans le viewport
 * Compatible avec tous les navigateurs modernes
 */

class SectionProlongement {
    constructor() {
        this.sections = document.querySelectorAll('.c-prolongement--animate');
        this.observerOptions = {
            root: null, // viewport
            rootMargin: '0px 0px -100px 0px', // trigger 100px avant le bas du viewport
            threshold: 0.1 // 10% de la section visible
        };

        this.init();
    }

    init() {
        if (this.sections.length === 0) {
            return;
        }

        // Vérification du support IntersectionObserver
        if ('IntersectionObserver' in window) {
            this.observeWithIntersectionObserver();
        } else {
            // Fallback pour les anciens navigateurs : afficher directement
            this.showAllSections();
        }
    }

    /**
     * Utilise IntersectionObserver pour détecter l'entrée dans le viewport
     */
    observeWithIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Ajouter la classe pour déclencher l'animation
                    entry.target.classList.add('is-visible');

                    // Optionnel : arrêter d'observer après l'animation
                    observer.unobserve(entry.target);
                }
            });
        }, this.observerOptions);

        this.sections.forEach(section => {
            observer.observe(section);
        });
    }

    /**
     * Fallback : affiche toutes les sections sans animation
     */
    showAllSections() {
        this.sections.forEach(section => {
            section.classList.add('is-visible');
        });
    }

    /**
     * Méthode publique pour réinitialiser les animations
     */
    reset() {
        this.sections.forEach(section => {
            section.classList.remove('is-visible');
        });
        this.init();
    }
}

// Auto-initialisation au chargement du DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.sectionProlongement = new SectionProlongement();
    });
} else {
    // DOM déjà chargé
    window.sectionProlongement = new SectionProlongement();
}

// Export pour usage en module (optionnel)
export default SectionProlongement;
