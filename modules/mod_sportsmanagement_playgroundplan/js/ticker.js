(() => {
    const initialise = (container) => {
        const items = Array.from(container.querySelectorAll('.jsm-playgroundplan-item'));

        if (items.length <= 1) {
            return;
        }

        let index = 0;
        let timer = null;

        const show = (nextIndex) => {
            items[index].hidden = true;
            index = nextIndex % items.length;
            items[index].hidden = false;
        };

        const play = () => {
            if (timer !== null) {
                return;
            }

            timer = window.setInterval(() => show(index + 1), 4000);
        };

        const stop = () => {
            if (timer === null) {
                return;
            }

            window.clearInterval(timer);
            timer = null;
        };

        container.addEventListener('mouseenter', stop);
        container.addEventListener('mouseleave', play);
        play();
    };

    const boot = () => {
        document.querySelectorAll('[data-jsm-playgroundplan-ticker]').forEach(initialise);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();
