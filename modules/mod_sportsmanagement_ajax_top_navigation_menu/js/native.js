(() => {
    'use strict';

    const optionData = (payload) => {
        if (Array.isArray(payload)) {
            return payload;
        }
        if (payload && Array.isArray(payload.data)) {
            return payload.data;
        }
        if (payload?.data && Array.isArray(payload.data.data)) {
            return payload.data.data;
        }
        return [];
    };

    const request = async (config, task, parameters = {}) => {
        const url = new URL('index.php', config.baseUrl);
        url.searchParams.set('option', 'com_sportsmanagement');
        url.searchParams.set('format', 'json');
        url.searchParams.set('tmpl', 'component');
        url.searchParams.set('task', `ajax.${task}`);

        Object.entries(parameters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`SportsManagement AJAX request failed with HTTP ${response.status}`);
        }

        return response.json();
    };

    const selectValue = (root, id) => root.querySelector(`#${CSS.escape(id)}`)?.value ?? '';

    const replaceOptions = (select, payload) => {
        if (!select) {
            return;
        }

        const items = optionData(payload);
        const fragment = document.createDocumentFragment();

        items.forEach((item) => {
            const option = document.createElement('option');
            const value = item?.value ?? item?.id ?? '';
            const text = item?.text ?? item?.name ?? value;
            option.value = String(value);
            option.textContent = String(text);
            fragment.appendChild(option);
        });

        select.replaceChildren(fragment);
        select.dispatchEvent(new Event('change:options', { bubbles: true }));
    };

    const clear = (root, ids) => {
        ids.forEach((id) => {
            const element = root.querySelector(`#${CSS.escape(id)}`);
            if (element) {
                element.replaceChildren();
            }
        });
    };

    const setBusy = (root, config, busy) => {
        const pagination = root.querySelector('#pagination');
        if (!pagination) {
            return;
        }

        pagination.querySelectorAll('[data-jsm-ajax-loader]').forEach((item) => item.remove());
        if (!busy) {
            return;
        }

        const item = document.createElement('li');
        item.dataset.jsmAjaxLoader = '1';
        item.className = 'nav-item';
        const image = document.createElement('img');
        image.src = new URL(config.loader, config.baseUrl).toString();
        image.alt = '';
        item.appendChild(image);
        pagination.appendChild(item);
    };

    const renderProjectLinks = async (root, config, projectId) => {
        const list = root.querySelector('#ajax-nav-list');
        if (!list || !config.showNavLinks) {
            return;
        }

        list.replaceChildren();
        if (!projectId || Number(projectId) <= 0) {
            return;
        }

        setBusy(root, config, true);

        try {
            const requests = config.navpoint.map(async (view, index) => {
                const label = config.navpointLabel[index] ?? '';
                if (!view) {
                    return null;
                }
                if (view === 'separator') {
                    return { separator: true, linktext: label };
                }

                const payload = await request(config, 'getLink', {
                    view,
                    project_id: projectId,
                    linktext: label,
                });
                const data = payload?.data && !Array.isArray(payload.data) ? payload.data : payload;
                return data?.data && !Array.isArray(data.data) ? data.data : data;
            });

            const links = await Promise.all(requests);
            const fragment = document.createDocumentFragment();

            links.forEach((item) => {
                if (!item) {
                    return;
                }

                const li = document.createElement('li');
                li.className = item.separator ? 'nav-item separator' : 'nav-item';

                if (item.separator) {
                    li.textContent = String(item.linktext ?? '');
                } else if (item.link) {
                    const anchor = document.createElement('a');
                    anchor.href = String(item.link);
                    anchor.textContent = String(item.linktext ?? '');
                    li.appendChild(anchor);
                } else {
                    return;
                }

                fragment.appendChild(li);
            });

            list.appendChild(fragment);
        } catch (error) {
            console.warn('SportsManagement AJAX top navigation links could not be refreshed.', error);
        } finally {
            setBusy(root, config, false);
        }
    };

    const bindFederation = (root, config, federation) => {
        const suffix = `${federation}${config.moduleId}`;
        const ids = {
            federation: `jlamtopfederation${suffix}`,
            assoc: `jlamtopassoc${suffix}`,
            subassoc: `jlamtopsubassoc${suffix}`,
            subsubassoc: `jlamtopsubsubassoc${suffix}`,
            subsubsubassoc: `jlamtopsubsubsubassoc${suffix}`,
            league: `jlamtopleagues${suffix}`,
            project: `jlamtopprojects${suffix}`,
            team: `jlamtopteams${suffix}`,
        };

        const element = (name) => root.querySelector(`#${CSS.escape(ids[name])}`);
        const country = () => selectValue(root, ids.federation);

        element('federation')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.assoc, ids.subassoc, ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            try {
                const [assocs, leagues] = await Promise.all([
                    request(config, 'getcountryassoc', { country: value }),
                    request(config, 'getAssocLeagueSelect', { country: value }),
                ]);
                replaceOptions(element('assoc'), assocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement federation navigation could not be refreshed.', error);
            }
        });

        element('assoc')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.subassoc, ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubAssocSelect', { assoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                replaceOptions(element('subassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement association navigation could not be refreshed.', error);
            }
        });

        element('subassoc')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubSubAssocSelect', { subassoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                replaceOptions(element('subsubassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement sub-association navigation could not be refreshed.', error);
            }
        });

        element('subsubassoc')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubSubAssocSelect', { subassoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                replaceOptions(element('subsubsubassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement lower association navigation could not be refreshed.', error);
            }
        });

        element('subsubsubassoc')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.league, ids.project, ids.team]);
            try {
                const leagues = await request(config, 'getAssocLeagueSelect', {
                    country: country(),
                    assoc_id: value,
                });
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement association leagues could not be refreshed.', error);
            }
        });

        element('league')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.project, ids.team]);
            try {
                replaceOptions(element('project'), await request(config, 'getProjectSelect', { league_id: value }));
            } catch (error) {
                console.warn('SportsManagement projects could not be refreshed.', error);
            }
        });

        element('project')?.addEventListener('change', async (event) => {
            const value = event.currentTarget.value;
            clear(root, [ids.team]);
            try {
                const teams = await request(config, 'getProjectTeams', { project_id: value });
                replaceOptions(element('team'), teams);
                await renderProjectLinks(root, config, value);
            } catch (error) {
                console.warn('SportsManagement project navigation could not be refreshed.', error);
            }
        });
    };

    const boot = (root) => {
        const moduleId = Number(root.dataset.moduleId || 0);
        if (!moduleId || typeof Joomla === 'undefined' || typeof Joomla.getOptions !== 'function') {
            return;
        }

        const config = Joomla.getOptions(`mod_sportsmanagement_ajax_top_navigation_menu.${moduleId}`);
        if (!config) {
            return;
        }

        (config.federations || []).forEach((federation) => bindFederation(root, config, federation));
    };

    const initialise = () => {
        document.querySelectorAll('[data-jsm-ajax-top-navigation]').forEach(boot);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, { once: true });
    } else {
        initialise();
    }
})();
