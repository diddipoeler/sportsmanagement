(() => {
    'use strict';

    const request = async (container, action, params = {}) => {
        const endpoint = container.dataset.endpoint || window.location.href;
        const url = new URL(endpoint, window.location.href);
        url.searchParams.set('action', action);

        Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));

        const response = await fetch(url.toString(), {
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.text();
    };

    const initialise = (container) => {
        container.querySelector('.turtushout-warning')?.remove();

        const status = container.querySelector('.turtushout-status');
        const shout = container.querySelector('.turtushout-shout');
        const form = container.querySelector('.turtushout-form');
        const timeout = Math.max(1000, Number(container.dataset.updateTimeout || 10000));

        if (status) {
            status.hidden = false;
        }

        const refresh = async () => {
            if (status) {
                status.textContent = 'Aktualisierung läuft...';
            }

            try {
                const html = await request(container, 'turtushout_shouts');

                if (shout) {
                    shout.innerHTML = html;
                }

                if (status) {
                    status.textContent = 'Spiele sind aktualisiert';
                }
            } catch (error) {
                if (status) {
                    status.textContent = error.message;
                }
            } finally {
                window.setTimeout(refresh, timeout);
            }
        };

        if (form) {
            request(container, 'turtushout_token')
                .then((token) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ts';
                    input.value = token.trim();
                    form.append(input);
                    form.hidden = false;
                })
                .catch(() => {});

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (status) {
                    status.textContent = 'Sending...';
                }

                const values = Object.fromEntries(new FormData(form).entries());

                try {
                    const result = (await request(container, 'turtushout_shout', values)).trim();

                    if (result !== 'Shouted!') {
                        if (status) {
                            status.textContent = result;
                        }
                        return;
                    }
                } catch (error) {
                    if (status) {
                        status.textContent = error.message;
                    }
                    return;
                }

                refresh();
            });
        }

        window.setTimeout(refresh, timeout);
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-sportsmanagement-liveticker').forEach(initialise);
    });
})();
