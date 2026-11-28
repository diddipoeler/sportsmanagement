(() => {
    'use strict';

    const decode = (value) => {
        if (typeof value !== 'string') {
            return value;
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            return value;
        }
    };

    const unwrap = (payload) => {
        let value = decode(payload);

        for (let depth = 0; depth < 3; depth++) {
            if (Array.isArray(value)) {
                return value;
            }
            if (!value || typeof value !== 'object' || !Object.prototype.hasOwnProperty.call(value, 'data')) {
                return decode(value);
            }
            value = decode(value.data);
        }

        return decode(value);
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

        const items = unwrap(payload);
        if (payload?.success === false || !Array.isArray(items)) return;

        // Choices may rebuild the native select with only the selected item.
        // Cache the real initial placeholder instead of treating a selected project as one.
        const placeholder = select.jsmPlaceholder?.cloneNode(true) ?? null;
        const includeSeason = Number(form.elements.include_season?.value ?? 0);
        const fragment = document.createDocumentFragment();

        if (placeholder) {
            fragment.appendChild(placeholder);
        }

        const seen = new Set(placeholder ? [placeholder.value] : []);
        let validCount = 0;
        items.forEach((item) => {
            if (!item || !['string', 'number'].includes(typeof item.value) || typeof item.text !== 'string') return;
            const option = document.createElement('option');
            option.value = String(item.value);
            validCount++;
            if (seen.has(option.value)) return;
            seen.add(option.value);
            const text = String(item.text ?? '');
            const season = String(item.season_name ?? '');
            option.textContent = includeSeason === 2
                ? `${text} - ${season}`
                : includeSeason === 1
                    ? `${season} - ${text}`
                    : text;
            fragment.appendChild(option);
        });

        if (items.length && !validCount) return;

        const choices = select.closest('joomla-field-fancy-select')?.choicesInstance;
        if (choices && typeof choices.clearStore === 'function' && typeof choices.setChoices === 'function') {
            const options = Array.from(fragment.children, (option, index) => ({
                value: option.value,
                label: option.textContent,
                selected: index === 0,
            }));
            choices.clearStore();
            choices.setChoices(options, 'value', 'label', true);
        } else {
            select.replaceChildren(fragment);
            select.selectedIndex = select.options.length ? 0 : -1;
        }
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

        const projectSelect = form.elements.p;
        if (projectSelect) {
            const first = Array.from(projectSelect.options).find(option => option.value === '' || option.value === '0');
            projectSelect.jsmPlaceholder = first?.cloneNode(true) ?? null;
        }
        let projectRequestVersion = 0;
        root.querySelectorAll('.jlnav-select').forEach((select) => {
            select.addEventListener('change', async () => {
                const version = ++projectRequestVersion;
                hideDependentItems(root);
                try {
                    const payload = await request('getprojectsoptions', {
                        s: form.elements.s?.value ?? '',
                        l: form.elements.l?.value ?? '',
                        o: form.elements.o?.value ?? '',
                        d: form.elements.d?.value ?? '',
                    });
                    if (version === projectRequestVersion) updateProjects(form, payload);
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
