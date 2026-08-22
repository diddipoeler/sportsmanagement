(() => {
    'use strict';

    const initialise = (container) => {
        const status = container.querySelector('.turtushout-status');
        const shout = container.querySelector('.turtushout-shout');
        const moduleId = Number.parseInt(container.dataset.moduleId || '0', 10);
        const timeout = Math.max(1000, Number(container.dataset.updateTimeout || 10000));
        const refreshEndpoint = container.dataset.refreshUrl || '';

        if (!shout || moduleId <= 0 || refreshEndpoint === '') {
            return;
        }

        let timerId = 0;
        let controller = null;

        const schedule = () => {
            window.clearTimeout(timerId);
            timerId = window.setTimeout(refresh, timeout);
        };

        const refresh = async () => {
            if (document.hidden) {
                schedule();
                return;
            }

            controller?.abort();
            controller = new AbortController();

            if (status) {
                status.textContent = 'Aktualisierung läuft...';
            }

            try {
                const url = new URL(refreshEndpoint, window.location.href);
                url.searchParams.set('module_id', String(moduleId));

                const response = await fetch(url.toString(), {
                    credentials: 'same-origin',
                    headers: {'Accept': 'text/html'},
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                shout.innerHTML = await response.text();

                if (status) {
                    status.textContent = 'Spiele sind aktualisiert';
                }
            } catch (error) {
                if (error?.name !== 'AbortError' && status) {
                    status.textContent = error instanceof Error ? error.message : 'Aktualisierung fehlgeschlagen';
                }
            } finally {
                schedule();
            }
        };

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refresh();
            }
        });

        schedule();
    };

    const initialiseAll = () => {
        document.querySelectorAll('.js-sportsmanagement-liveticker').forEach(initialise);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseAll, {once: true});
    } else {
        initialiseAll();
    }
})();
