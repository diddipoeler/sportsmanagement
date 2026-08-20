(() => {
    'use strict';

    document.querySelectorAll('[data-jsm-teamplayers-carousel]').forEach((root) => {
        const cards = Array.from(root.querySelectorAll('[data-jsm-teamplayers-card]'));
        if (cards.length < 2) {
            return;
        }

        const track = root.querySelector('.jsm-teamplayers-track');
        const pagination = root.querySelector('[data-jsm-teamplayers-pagination]');
        const mode = root.dataset.mode || 'horizontal';
        let index = 0;

        const show = (nextIndex) => {
            index = (nextIndex + cards.length) % cards.length;
            cards.forEach((card, cardIndex) => card.classList.toggle('is-active', cardIndex === index));

            if (mode !== 'fade') {
                cards[index].scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'nearest'});
            }

            if (pagination) {
                pagination.textContent = `${index + 1} / ${cards.length}`;
            }
        };

        root.querySelector('[data-jsm-teamplayers-prev]')?.addEventListener('click', () => show(index - 1));
        root.querySelector('[data-jsm-teamplayers-next]')?.addEventListener('click', () => show(index + 1));

        if (root.dataset.auto === '1') {
            const speed = Math.max(1500, Number.parseInt(root.dataset.speed || '500', 10) * 4);
            window.setInterval(() => show(index + 1), speed);
        }

        if (track && mode !== 'fade') {
            track.addEventListener('scrollend', () => {
                const axisStart = mode === 'vertical' ? track.scrollTop : track.scrollLeft;
                let nearest = 0;
                let distance = Number.POSITIVE_INFINITY;
                cards.forEach((card, cardIndex) => {
                    const cardStart = mode === 'vertical' ? card.offsetTop : card.offsetLeft;
                    const delta = Math.abs(cardStart - axisStart);
                    if (delta < distance) {
                        distance = delta;
                        nearest = cardIndex;
                    }
                });
                index = nearest;
                if (pagination) {
                    pagination.textContent = `${index + 1} / ${cards.length}`;
                }
            });
        }
    });
})();
