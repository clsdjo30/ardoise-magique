(function() {
    'use strict';

    function initTemplateCarousel() {
        const carouselContainer = document.querySelector('.template-carousel-container');
        if (!carouselContainer) return;

        const templateGrid = carouselContainer.querySelector('.template-grid');
        const templateOptions = carouselContainer.querySelectorAll('.template-option');
        const prevBtn = carouselContainer.querySelector('.carousel-nav-btn.prev');
        const nextBtn = carouselContainer.querySelector('.carousel-nav-btn.next');
        const dotsContainer = carouselContainer.querySelector('.carousel-dots');

        if (templateOptions.length === 0 || !templateGrid) return;

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

        function updateActiveDot() {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('.carousel-dot');
            if (!dots.length) return;
            const viewWidth = templateGrid.clientWidth || 1;
            const activeIndex = Math.min(dots.length - 1, Math.round(templateGrid.scrollLeft / viewWidth));
            dots.forEach((dot, idx) => {
                dot.classList.toggle('active', idx === activeIndex);
            });
        }

        function updateNavButtons() {
            if (prevBtn && nextBtn) {
                const scrollLeft = templateGrid.scrollLeft;
                const maxScroll = templateGrid.scrollWidth - templateGrid.clientWidth;
                prevBtn.disabled = scrollLeft <= 0;
                nextBtn.disabled = scrollLeft >= maxScroll - 1;
            }
            updateActiveDot();
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                templateGrid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                templateGrid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        function getPageCount() {
            const viewWidth = templateGrid.clientWidth || 1;
            return Math.max(1, Math.ceil(templateGrid.scrollWidth / viewWidth));
        }

        function renderDots() {
            if (!dotsContainer) return;

            const pages = getPageCount();
            dotsContainer.innerHTML = '';

            if (pages <= 1) {
                dotsContainer.style.display = 'none';
                return;
            }

            dotsContainer.style.display = '';

            for (let i = 0; i < pages; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'carousel-dot';
                dot.setAttribute('aria-label', `Aller au slide ${i + 1}`);
                dot.addEventListener('click', () => {
                    const targetLeft = templateGrid.clientWidth * i;
                    templateGrid.scrollTo({ left: targetLeft, behavior: 'smooth' });
                });
                dotsContainer.appendChild(dot);
            }

            updateActiveDot();
        }

        templateGrid.addEventListener('scroll', updateNavButtons);
        window.addEventListener('resize', () => {
            renderDots();
            updateNavButtons();
        });
        updateNavButtons();
        renderDots();

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
                    zoomImage.alt = templateName || '';
                    zoomTitle.textContent = templateName || '';
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
