import './bootstrap';
import './pwa';

const revealObserver = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                if (entry.target.dataset.revealRepeat !== 'true') {
                    revealObserver.unobserve(entry.target);
                }
            } else if (entry.target.dataset.revealRepeat === 'true') {
                entry.target.classList.remove('is-visible');
            }
        });
    },
    {
        threshold: 0.2,
        rootMargin: '0px 0px -8% 0px',
    }
);

function bindRevealElements(scope = document) {
    const revealEls = scope.querySelectorAll('[data-reveal]:not([data-reveal-bound="1"])');
    revealEls.forEach((el) => {
        el.dataset.revealBound = '1';
        el.classList.add('reveal');

        const variant = (el.dataset.reveal || 'up').trim();
        if (variant === 'left') {
            el.classList.add('reveal-left');
        } else if (variant === 'right') {
            el.classList.add('reveal-right');
        } else if (variant === 'zoom') {
            el.classList.add('reveal-zoom');
        }

        const delay = Number(el.dataset.revealDelay || 0);
        if (!Number.isNaN(delay) && delay > 0) {
            el.style.transitionDelay = `${delay}ms`;
        }

        revealObserver.observe(el);
    });
}

document.addEventListener('DOMContentLoaded', () => bindRevealElements());
document.addEventListener('livewire:navigated', () => bindRevealElements());
