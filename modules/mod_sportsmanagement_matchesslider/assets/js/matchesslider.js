(() => {
    'use strict';

    const selector = '[data-jsm-matchesslider]';
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const makeClone = (item) => {
        const clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.dataset.jsmMatchessliderClone = 'true';

        clone.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach((element) => {
            element.setAttribute('tabindex', '-1');
        });

        return clone;
    };

    const initialise = (root) => {
        if (root.dataset.jsmMatchessliderInitialised === 'true') {
            return;
        }

        root.dataset.jsmMatchessliderInitialised = 'true';
        const track = root.querySelector('[data-jsm-matchesslider-track]');
        const items = track ? Array.from(track.children) : [];

        if (!track || items.length < 2 || reduceMotion.matches) {
            root.classList.add('jsm-matchesslider--static');
            return;
        }

        const direction = root.dataset.scrollDirection === 'forwards' ? 'forwards' : 'backwards';
        const requestedSpeed = Number.parseFloat(root.dataset.scrollSpeed || '40');
        const speed = Number.isFinite(requestedSpeed) ? Math.min(240, Math.max(5, requestedSpeed)) : 40;
        const clones = items.map(makeClone);
        clones.forEach((clone) => track.appendChild(clone));

        let cycleWidth = 0;
        let position = 0;
        let previousTime = performance.now();
        let frameId = 0;

        const measure = () => {
            const firstOriginal = items[0];
            const firstClone = clones[0];
            const measuredWidth = firstClone.offsetLeft - firstOriginal.offsetLeft;

            if (measuredWidth <= 0) {
                return;
            }

            const previousWidth = cycleWidth;
            cycleWidth = measuredWidth;

            if (previousWidth === 0) {
                position = direction === 'backwards' ? cycleWidth : 0;
            } else if (cycleWidth !== previousWidth) {
                position = cycleWidth > 0 ? (position / previousWidth) * cycleWidth : 0;
            }

            position = Math.max(0, Math.min(cycleWidth, position));
            track.style.transform = `translate3d(${-position}px, 0, 0)`;
        };

        const animate = (time) => {
            const elapsed = Math.min(0.1, Math.max(0, (time - previousTime) / 1000));
            previousTime = time;

            if (cycleWidth > 0 && !document.hidden) {
                const distance = speed * elapsed;

                if (direction === 'forwards') {
                    position += distance;
                    while (position >= cycleWidth) {
                        position -= cycleWidth;
                    }
                } else {
                    position -= distance;
                    while (position <= 0) {
                        position += cycleWidth;
                    }
                }

                track.style.transform = `translate3d(${-position}px, 0, 0)`;
            }

            frameId = window.requestAnimationFrame(animate);
        };

        measure();

        const resizeObserver = 'ResizeObserver' in window
            ? new ResizeObserver(measure)
            : null;

        if (resizeObserver) {
            resizeObserver.observe(root);
        } else {
            window.addEventListener('resize', measure, {passive: true});
        }

        const stopForReducedMotion = (event) => {
            if (!event.matches) {
                return;
            }

            window.cancelAnimationFrame(frameId);
            resizeObserver?.disconnect();
            clones.forEach((clone) => clone.remove());
            track.style.transform = '';
            root.classList.add('jsm-matchesslider--static');
        };

        reduceMotion.addEventListener?.('change', stopForReducedMotion, {once: true});
        frameId = window.requestAnimationFrame(animate);
    };

    const initialiseAll = () => {
        document.querySelectorAll(selector).forEach(initialise);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseAll, {once: true});
    } else {
        initialiseAll();
    }
})();
