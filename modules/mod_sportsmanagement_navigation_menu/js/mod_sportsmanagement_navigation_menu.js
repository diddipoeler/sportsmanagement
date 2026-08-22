(() => {
    'use strict';

    const unwrap = (payload) => {
        if (Array.isArray(payload)) {
            return payload;
        }
        if (Array.isArray(payload?.data)) {
            return payload.data;
        }
        if (Array.isArray(payload?.data?.data)) {
            return payload.data.data;
        }
        return payload?.data?.data ?? payload?.data ?? payload;
    };

    const request = async (task, data) => {
        const body = new URLSearchParams(data);
        const response = await fetch(`index.php?option=com_sportsmanagement&task=ajax.${task}&tmpl=component&format=json`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        });

        if (!response.ok) {
            throw new Error(`SportsManagement navigation request failed with HTTP ${response.status}`);
        }

        return response.json();
    };

    const hideDependentItems = (root) => {
        root.querySelectorAll('.nav-item, .team-select, .division-select').forEach((item) => {
            item.hidden = true;
        });
    };

    const updateProjects = (form, payload) => {
        const select = form.elements.p;
        if (!select) {
            return;
        }

        const placeholder = select.options.length ? select.options[0].cloneNode(true) : null;
        const includeSeason = Number(form.elements.include_season?.value ?? 0);
        const fragment = document.createDocumentFragment();

        if (placeholder) {
            fragment.appendChild(placeholder);
        }

        (unwrap(payload) || []).forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item.value ?? '');
            const text = String(item.text ?? '');
            const season = String(item.season_name ?? '');
            option.textContent = includeSeason === 2
                ? `${text} - ${season}`
                : includeSeason === 1
                    ? `${season} - ${text}`
                    : text;
            fragment.appendChild(option);
        });

        select.replaceChildren(fragment);
    };

    const redirect = async (form) => {
        const payload = await request('getroute', {
            view: form.elements.view?.value ?? 'ranking',
            p: form.elements.p?.value ?? 0,
            division: form.elements.d?.value ?? 0,
            tid: form.elements.tid?.value ?? 0,
        });
        const target = unwrap(payload);
        const route = typeof target === 'string' ? target : target?.link ?? target?.url ?? '';

        if (route) {
            window.location.assign(route);
        }
    };

    const boot = (root) => {
        const form = root.querySelector('form');
        if (!form) {
            return;
        }

        root.querySelectorAll('.jlnav-select').forEach((select) => {
            select.addEventListener('change', async () => {
                hideDependentItems(root);
                try {
                    const payload = await request('getprojectsoptions', {
                        s: form.elements.s?.value ?? '',
                        l: form.elements.l?.value ?? '',
                        o: form.elements.o?.value ?? '',
                        d: form.elements.d?.value ?? '',
                    });
                    updateProjects(form, payload);
                } catch (error) {
                    console.warn('SportsManagement project navigation could not be refreshed.', error);
                }
            });
        });

        root.querySelector('.jlnav-project')?.addEventListener('change', async (event) => {
            if (Number(event.currentTarget.value) <= 0) {
                return;
            }
            try {
                await redirect(form);
            } catch (error) {
                console.warn('SportsManagement project route could not be resolved.', error);
            }
        });

        root.querySelector('.jlnav-division')?.addEventListener('change', async () => {
            try {
                await redirect(form);
            } catch (error) {
                console.warn('SportsManagement division route could not be resolved.', error);
            }
        });

        root.querySelector('.jlnav-team')?.addEventListener('change', async () => {
            if (form.elements.teamview) {
                form.elements.view.value = form.elements.teamview.value;
            }
            try {
                await redirect(form);
            } catch (error) {
                console.warn('SportsManagement team route could not be resolved.', error);
            }
        });
    };

    const initialise = () => {
        document.querySelectorAll('[data-jsm-navigation-menu]').forEach(boot);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
