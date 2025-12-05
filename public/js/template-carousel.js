(function() {
    'use strict';

    // Attendre que le DOM soit chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateCarousel);
    } else {
        initTemplateCarousel();
    }

    function initTemplateCarousel() {
        // Trouver le conteneur du champ template
        const templateContainer = document.querySelector('.template-selector-wrapper');
        if (!templateContainer) return;

        const templateWidget = templateContainer.querySelector('#Ardoise_template, [id$="_template"]');
        if (!templateWidget) return;

        // Récupérer tous les form-check
        const formChecks = templateWidget.querySelectorAll('.form-check');
        if (formChecks.length === 0) return;

        // Créer la structure du carrousel
        const carouselHTML = `
            <div class="template-carousel-container">
                <button type="button" class="carousel-nav-btn prev" aria-label="Précédent">‹</button>
                <div class="template-grid"></div>
                <button type="button" class="carousel-nav-btn next" aria-label="Suivant">›</button>
            </div>
            <div class="template-zoom-modal" id="templateZoomModal">
                <div class="template-zoom-content">
                    <button type="button" class="template-zoom-close" aria-label="Fermer">×</button>
                    <img src="" alt="" class="template-zoom-image" id="zoomImage">
                    <div class="template-zoom-title" id="zoomTitle"></div>
                </div>
            </div>
        `;

        // Insérer le carrousel
        templateWidget.insertAdjacentHTML('beforebegin', carouselHTML);
        const grid = templateContainer.querySelector('.template-grid');

        // Transformer chaque form-check en template-option
        formChecks.forEach(formCheck => {
            const input = formCheck.querySelector('input[type="radio"]');
            const label = formCheck.querySelector('label');

            if (!input || !label) return;

            const templateImage = input.getAttribute('data-template-image');
            const templateLabel = input.getAttribute('data-template-label');
            const templateValue = input.getAttribute('data-template-value');

            // Créer la nouvelle structure
            const optionDiv = document.createElement('div');
            optionDiv.className = 'template-option';
            if (input.checked) {
                optionDiv.classList.add('selected');
            }

            // Créer le contenu
            const contentHTML = `
                <div class="template-content">
                    <img src="${templateImage}" alt="${templateLabel}" class="template-thumbnail">
                    <span class="template-label">${templateLabel}</span>
                </div>
                <div class="template-zoom-btn" data-template-name="${templateLabel}" data-template-image="${templateImage}"></div>
            `;

            // Ajouter l'input et le label
            optionDiv.appendChild(input);
            input.style.position = 'absolute';
            input.style.opacity = '0';
            input.style.width = '0';
            input.style.height = '0';

            const newLabel = document.createElement('label');
            newLabel.setAttribute('for', input.id);
            newLabel.innerHTML = contentHTML;
            optionDiv.appendChild(newLabel);

            grid.appendChild(optionDiv);
        });

        // Cacher l'ancien widget
        templateWidget.style.display = 'none';

        // Initialiser les fonctionnalités
        initCarouselFeatures(templateContainer);
    }

    function initCarouselFeatures(container) {
        const templateOptions = container.querySelectorAll('.template-option');
        const carouselContainer = container.querySelector('.template-grid');
        const prevBtn = container.querySelector('.carousel-nav-btn.prev');
        const nextBtn = container.querySelector('.carousel-nav-btn.next');
        const zoomModal = document.getElementById('templateZoomModal');
        const zoomImage = document.getElementById('zoomImage');
        const zoomTitle = document.getElementById('zoomTitle');
        const zoomClose = zoomModal ? zoomModal.querySelector('.template-zoom-close') : null;

        // Gestion de la sélection
        templateOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            if (!radio) return;

            option.addEventListener('click', function(e) {
                if (e.target.classList.contains('template-zoom-btn')) return;
                if (e.target !== radio) {
                    radio.click();
                }
            });

            radio.addEventListener('change', function() {
                templateOptions.forEach(opt => opt.classList.remove('selected'));
                if (this.checked) {
                    option.classList.add('selected');
                }
            });
        });

        // Gestion du carrousel
        const scrollAmount = 250;

        function updateNavButtons() {
            if (!prevBtn || !nextBtn || !carouselContainer) return;

            const scrollLeft = carouselContainer.scrollLeft;
            const maxScroll = carouselContainer.scrollWidth - carouselContainer.clientWidth;

            prevBtn.disabled = scrollLeft <= 0;
            nextBtn.disabled = scrollLeft >= maxScroll - 1;
        }

        if (prevBtn && carouselContainer) {
            prevBtn.addEventListener('click', () => {
                carouselContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn && carouselContainer) {
            nextBtn.addEventListener('click', () => {
                carouselContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        if (carouselContainer) {
            carouselContainer.addEventListener('scroll', updateNavButtons);
            window.addEventListener('resize', updateNavButtons);
            updateNavButtons();
        }

        // Gestion du zoom
        const zoomButtons = container.querySelectorAll('.template-zoom-btn');

        zoomButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();

                const imageSrc = this.getAttribute('data-template-image');
                const templateName = this.getAttribute('data-template-name');

                if (imageSrc && zoomImage && zoomModal) {
                    zoomImage.src = imageSrc;
                    zoomImage.alt = templateName;
                    zoomTitle.textContent = templateName;
                    zoomModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Fermer la modal
        function closeZoomModal() {
            if (zoomModal) {
                zoomModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        if (zoomClose) {
            zoomClose.addEventListener('click', closeZoomModal);
        }

        if (zoomModal) {
            zoomModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeZoomModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && zoomModal && zoomModal.classList.contains('active')) {
                closeZoomModal();
            }
        });
    }
})();
