/**
 * Hero section animations and interactions
 */

export default function initHero() {
    const hero = document.querySelector('.hero');
    if (!hero) return;

    // Parallax effect on scroll for background decorations
    const handleScroll = () => {
        const scrolled = window.scrollY;
        const decos = hero.querySelectorAll('.hero__deco');

        decos.forEach((deco, index) => {
            const speed = 0.3 + (index * 0.1);
            const yPos = scrolled * speed;
            deco.style.transform = `translateY(${yPos}px) rotate(${15 + (index * 10)}deg)`;
        });
    };

    // Fade in animation on page load
    const fadeInHero = () => {
        const content = hero.querySelector('.hero__content');
        const visual = hero.querySelector('.hero__visual');

        if (content) {
            setTimeout(() => {
                content.style.opacity = '0';
                content.style.transform = 'translateY(30px)';
                content.style.transition = 'opacity 0.8s ease, transform 0.8s ease';

                requestAnimationFrame(() => {
                    content.style.opacity = '1';
                    content.style.transform = 'translateY(0)';
                });
            }, 100);
        }

        if (visual) {
            setTimeout(() => {
                visual.style.opacity = '0';
                visual.style.transform = 'translateX(30px) scale(0.95)';
                visual.style.transition = 'opacity 1s ease 0.2s, transform 1s ease 0.2s';

                requestAnimationFrame(() => {
                    visual.style.opacity = '1';
                    visual.style.transform = 'translateX(0) scale(1)';
                });
            }, 100);
        }
    };

    // Mouse movement parallax effect
    const handleMouseMove = (e) => {
        const { clientX, clientY } = e;
        const { innerWidth, innerHeight } = window;

        const xPos = (clientX / innerWidth - 0.5) * 20;
        const yPos = (clientY / innerHeight - 0.5) * 20;

        const imageWrapper = hero.querySelector('.hero__image-wrapper');
        if (imageWrapper) {
            imageWrapper.style.transform = `translate(${xPos}px, ${yPos}px)`;
        }

        const rings = hero.querySelectorAll('.hero__ring');
        rings.forEach((ring, index) => {
            const speed = 1 + (index * 0.5);
            ring.style.transform = `translate(-50%, -50%) translate(${xPos * speed}px, ${yPos * speed}px)`;
        });
    };

    // Initialize animations
    fadeInHero();

    // Add event listeners with throttling for performance
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) return;
        scrollTimeout = setTimeout(() => {
            handleScroll();
            scrollTimeout = null;
        }, 10);
    }, { passive: true });

    // Mouse move effect only on desktop
    if (window.innerWidth > 968) {
        let mouseMoveTimeout;
        hero.addEventListener('mousemove', (e) => {
            if (mouseMoveTimeout) return;
            mouseMoveTimeout = setTimeout(() => {
                handleMouseMove(e);
                mouseMoveTimeout = null;
            }, 16); // ~60fps
        }, { passive: true });

        // Reset position when mouse leaves
        hero.addEventListener('mouseleave', () => {
            const imageWrapper = hero.querySelector('.hero__image-wrapper');
            const rings = hero.querySelectorAll('.hero__ring');

            if (imageWrapper) {
                imageWrapper.style.transform = 'translate(0, 0)';
            }

            rings.forEach((ring) => {
                ring.style.transform = 'translate(-50%, -50%)';
            });
        });
    }

    // Smooth scroll for CTA buttons
    const ctaButtons = hero.querySelectorAll('.hero__cta');
    ctaButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const href = button.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}
