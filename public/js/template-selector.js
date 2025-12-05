(function() {
    'use strict';

    function initTemplateCarousel() {
        const carouselContainer = document.querySelector('.template-carousel-container');
        if (!carouselContainer) return;

        const templateGrid = carouselContainer.querySelector('.template-grid');
        const templateOptions = carouselContainer.querySelectorAll('.template-option');
        const prevBtn = carouselContainer.querySelector('.carousel-nav-btn.prev');
        const nextBtn = carouselContainer.querySelector('.carousel-nav-btn.next');

        if (templateOptions.length === 0) return;

        // Gestion de la sélection
        templateOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            if (!radio) return;

            option.addEventListener('click', function(e) {
                if (e.target.classList.contains('template-zoom-btn')) return;
                if (e.target !== radio && !e.target.closest('label')) {
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
            if (!prevBtn || !nextBtn || !templateGrid) return;
            const scrollLeft = templateGrid.scrollLeft;
            const maxScroll = templateGrid.scrollWidth - templateGrid.clientWidth;
            prevBtn.disabled = scrollLeft <= 0;
            nextBtn.disabled = scrollLeft >= maxScroll - 1;
        }

        if (prevBtn && templateGrid) {
            prevBtn.addEventListener('click', () => {
                templateGrid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn && templateGrid) {
            nextBtn.addEventListener('click', () => {
                templateGrid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        if (templateGrid) {
            templateGrid.addEventListener('scroll', updateNavButtons);
            window.addEventListener('resize', updateNavButtons);
            updateNavButtons();
        }

        // Gestion du zoom
        const zoomButtons = carouselContainer.querySelectorAll('.template-zoom-btn');

        // Créer la modal de zoom une seule fois
        let zoomModal = document.getElementById('templateZoomModal');
        if (!zoomModal) {
            zoomModal = document.createElement('div');
            zoomModal.id = 'templateZoomModal';
            zoomModal.className = 'template-zoom-modal';
            zoomModal.innerHTML = `
                <div class="template-zoom-content">
                    <button type="button" class="template-zoom-close" aria-label="Fermer">×</button>
                    <img src="" alt="" class="template-zoom-image" id="zoomImage">
                    <div class="template-zoom-title" id="zoomTitle"></div>
                </div>
            `;
            document.body.appendChild(zoomModal);
        }

        const zoomImage = document.getElementById('zoomImage');
        const zoomTitle = document.getElementById('zoomTitle');
        const zoomClose = zoomModal.querySelector('.template-zoom-close');

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
                if (e.target === this) closeZoomModal();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && zoomModal && zoomModal.classList.contains('active')) {
                closeZoomModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateCarousel);
    } else {
        initTemplateCarousel();
    }
})();
