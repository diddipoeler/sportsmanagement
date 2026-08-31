(() => {
    'use strict';

    const decodeResult = (payload) => {
        let value = payload;

        for (let i = 0; i < 3; i += 1) {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                    continue;
                } catch (error) {
                    return null;
                }
            }

            if (value && typeof value === 'object' && 'data' in value) {
                value = value.data;
                continue;
            }

            break;
        }

        if (Array.isArray(value)) {
            value = value[0] ?? null;
        }

        return value && typeof value === 'object' ? value : null;
    };

    const bind = (root) => {
        if (root.dataset.jsmCreateArticlesBound === '1') {
            return;
        }

        const button = root.querySelector('[data-jsm-create-project-articles]');
        const form = root.querySelector('[data-jsm-create-project-articles-form]');
        const status = root.querySelector('[data-jsm-create-project-articles-status]');
        const endpoint = root.dataset.endpoint || '';

        if (!button || !form || !status || !endpoint) {
            return;
        }

        root.dataset.jsmCreateArticlesBound = '1';

        button.addEventListener('click', async () => {
            button.disabled = true;
            status.textContent = root.dataset.creatingText || '';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const payload = await response.json();
                const result = decodeResult(payload);

                if (!response.ok || !result) {
                    throw new Error(payload?.message || `HTTP ${response.status}`);
                }

                status.textContent = (root.dataset.createdText || '')
                    .replace('%1$d', String(Number(result.created || 0)))
                    .replace('%2$d', String(Number(result.skipped || 0)));
            } catch (error) {
                status.textContent = root.dataset.errorText || '';

                if (window.console) {
                    console.error('SportsManagement new project article creation failed', error);
                }
            } finally {
                button.disabled = false;
            }
        });
    };

    const initialise = () => {
        document.querySelectorAll('[data-jsm-new-project]').forEach(bind);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
