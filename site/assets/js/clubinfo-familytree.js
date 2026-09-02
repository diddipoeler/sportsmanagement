(() => {
    'use strict';

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const toggle = event.target.closest('[data-jsm-clubinfo-tree-toggle]');
        if (!toggle) {
            return;
        }

        const item = toggle.closest('li');
        const branch = item ? Array.from(item.children).find((child) => child.tagName === 'UL') : null;
        if (!(branch instanceof HTMLElement)) {
            return;
        }

        const expanded = toggle.getAttribute('aria-expanded') !== 'false';
        branch.hidden = expanded;
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');

        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('icon-minus-sign', !expanded);
            icon.classList.toggle('icon-plus-sign', expanded);
        }

        event.stopPropagation();
    });
})();
