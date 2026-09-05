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

    const linkData = (payload) => {
        let data = payload;
        while (data?.data && !Array.isArray(data.data) && typeof data.data === 'object') {
            data = data.data;
        }
        return data && typeof data === 'object' ? data : null;
    };

    const request = async (config, task, parameters = {}) => {
        const url = new URL(config.ajaxUrl || 'index.php', config.baseUrl || document.baseURI);
        url.searchParams.set('option', 'com_sportsmanagement');
        url.searchParams.set('format', 'json');
        url.searchParams.set('task', `ajax.${task}`);

        if (Number(config.cfgWhichDatabase) > 0) {
            url.searchParams.set('cfg_which_database', String(config.cfgWhichDatabase));
        }
        if (Number(config.itemId) > 0) {
            url.searchParams.set('Itemid', String(config.itemId));
        }

        Object.entries(parameters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, String(value));
            }
        });

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' },
        });
        const responseText = await response.text();

        if (!response.ok) {
            throw new Error(`SportsManagement AJAX request failed with HTTP ${response.status}`);
        }

        let payload;
        try {
            payload = JSON.parse(responseText);
        } catch (error) {
            throw new Error(`SportsManagement AJAX returned invalid JSON: ${responseText.slice(0, 180)}`, { cause: error });
        }

        if (payload?.success === false) {
            throw new Error(String(payload.message || 'SportsManagement AJAX request failed.'));
        }

        return payload;
    };

    const selectValue = (root, id) => root.querySelector(`#${CSS.escape(id)}`)?.value ?? '';

    const replaceOptions = (select, payload) => {
        if (!select) {
            return;
        }

        const fragment = document.createDocumentFragment();
        optionData(payload).forEach((item) => {
            const option = document.createElement('option');
            const value = item?.value ?? item?.id ?? '';
            const text = item?.text ?? item?.name ?? value;
            option.value = String(value);
            option.textContent = String(text);
            if (item?.project_type) {
                option.dataset.projectType = String(item.project_type);
            }
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

    const linkContext = (config, projectId, teamId = 0) => {
        const current = new URL(window.location.href);
        return {
            project_id: projectId,
            team_id: teamId,
            division_id: current.searchParams.get('division') || 0,
            tnid: current.searchParams.get('tnid') || 0,
            points: config.alltimePoints || '3,1,0',
        };
    };

    const renderLinks = async (list, root, config, views, labels, projectId, teamId, isCurrent) => {
        if (!list || !config.showNavLinks) {
            return;
        }

        list.replaceChildren();
        if (!projectId || Number(projectId) <= 0) {
            return;
        }

        const context = linkContext(config, projectId, teamId);
        const requests = views.map(async (view, index) => {
            const label = labels[index] ?? '';
            if (!view) {
                return null;
            }
            if (view === 'separator') {
                return { separator: true, linktext: label };
            }

            const payload = await request(config, 'getLink', {
                ...context,
                view,
                linktext: label,
            });
            return linkData(payload);
        });

        const links = await Promise.all(requests);
        if (!isCurrent()) {
            return;
        }

        const fragment = document.createDocumentFragment();
        links.forEach((item) => {
            if (!item) {
                return;
            }

            const li = document.createElement('li');
            li.className = item.separator ? 'nav-item separator' : 'nav-item';

            if (item.separator) {
                const span = document.createElement('span');
                span.className = 'nav-link disabled';
                span.textContent = String(item.linktext ?? '');
                li.appendChild(span);
            } else if (item.link) {
                const anchor = document.createElement('a');
                anchor.className = 'nav-link';
                anchor.href = String(item.link);
                anchor.textContent = String(item.linktext ?? '');
                li.appendChild(anchor);
            } else {
                return;
            }

            fragment.appendChild(li);
        });

        list.appendChild(fragment);
    };

    const ensureTeamLinkList = (teamSelect) => {
        const pane = teamSelect?.closest('.tab-pane');
        if (!pane) {
            return null;
        }

        let list = pane.querySelector('[data-jsm-team-nav-links]');
        if (!list) {
            list = document.createElement('ul');
            list.className = 'nav flex-column mt-3';
            list.dataset.jsmTeamNavLinks = '1';
            pane.appendChild(list);
        }
        return list;
    };

    const projectNavigation = (config, projectSelect) => {
        const views = [...(config.navpoint || [])];
        const labels = [...(config.navpointLabel || [])];
        const selectedProjectType = projectSelect?.selectedOptions?.[0]?.dataset?.projectType || '';

        if (config.showTournamentNavLinks || selectedProjectType === 'TOURNAMENT_MODE') {
            views.push('tournamentbracket');
            labels.push(config.tournamentText || '');
        }
        if (config.showAlltimeNavLinks) {
            views.push('rankingalltime');
            labels.push(config.alltimeText || '');
        }

        return { views, labels };
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
        const project = () => selectValue(root, ids.project);
        const team = () => selectValue(root, ids.team);
        let revision = 0;
        const nextRevision = () => ++revision;
        const isCurrent = (token) => () => token === revision;
        const projectList = () => root.querySelector('#ajax-nav-list');
        const teamList = () => ensureTeamLinkList(element('team'));

        const clearNavigation = () => {
            projectList()?.replaceChildren();
            teamList()?.replaceChildren();
        };

        element('federation')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.assoc, ids.subassoc, ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            clearNavigation();
            try {
                const [assocs, leagues] = await Promise.all([
                    request(config, 'getcountryassoc', { country: value }),
                    request(config, 'getAssocLeagueSelect', { country: value }),
                ]);
                if (!isCurrent(token)()) return;
                replaceOptions(element('assoc'), assocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement federation navigation could not be refreshed.', error);
            }
        });

        element('assoc')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.subassoc, ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            clearNavigation();
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubAssocSelect', { assoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                if (!isCurrent(token)()) return;
                replaceOptions(element('subassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement association navigation could not be refreshed.', error);
            }
        });

        element('subassoc')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.subsubassoc, ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            clearNavigation();
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubSubAssocSelect', { subassoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                if (!isCurrent(token)()) return;
                replaceOptions(element('subsubassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement sub-association navigation could not be refreshed.', error);
            }
        });

        element('subsubassoc')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.subsubsubassoc, ids.league, ids.project, ids.team]);
            clearNavigation();
            try {
                const [subassocs, leagues] = await Promise.all([
                    request(config, 'getCountrySubSubAssocSelect', { subassoc_id: value }),
                    request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value }),
                ]);
                if (!isCurrent(token)()) return;
                replaceOptions(element('subsubsubassoc'), subassocs);
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement lower association navigation could not be refreshed.', error);
            }
        });

        element('subsubsubassoc')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.league, ids.project, ids.team]);
            clearNavigation();
            try {
                const leagues = await request(config, 'getAssocLeagueSelect', { country: country(), assoc_id: value });
                if (!isCurrent(token)()) return;
                replaceOptions(element('league'), leagues);
            } catch (error) {
                console.warn('SportsManagement association leagues could not be refreshed.', error);
            }
        });

        element('league')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            clear(root, [ids.project, ids.team]);
            clearNavigation();
            try {
                const projects = await request(config, 'getProjectSelect', { league_id: event.currentTarget.value });
                if (!isCurrent(token)()) return;
                replaceOptions(element('project'), projects);
            } catch (error) {
                console.warn('SportsManagement projects could not be refreshed.', error);
            }
        });

        element('project')?.addEventListener('change', async (event) => {
            const token = nextRevision();
            const value = event.currentTarget.value;
            clear(root, [ids.team]);
            clearNavigation();
            setBusy(root, config, true);
            try {
                const teams = await request(config, 'getProjectTeams', { project_id: value });
                if (!isCurrent(token)()) return;
                replaceOptions(element('team'), teams);
                const navigation = projectNavigation(config, element('project'));
                await renderLinks(
                    projectList(), root, config, navigation.views, navigation.labels,
                    value, 0, isCurrent(token)
                );
            } catch (error) {
                console.warn('SportsManagement project navigation could not be refreshed.', error);
            } finally {
                if (isCurrent(token)()) setBusy(root, config, false);
            }
        });

        element('team')?.addEventListener('change', async () => {
            const token = nextRevision();
            const projectId = project();
            const teamId = team();
            setBusy(root, config, true);
            try {
                const navigation = projectNavigation(config, element('project'));
                await Promise.all([
                    renderLinks(
                        projectList(), root, config, navigation.views, navigation.labels,
                        projectId, teamId, isCurrent(token)
                    ),
                    renderLinks(
                        teamList(), root, config, config.teamNavpoint || [], config.teamNavpointLabel || [],
                        projectId, teamId, isCurrent(token)
                    ),
                ]);
            } catch (error) {
                console.warn('SportsManagement team navigation could not be refreshed.', error);
            } finally {
                if (isCurrent(token)()) setBusy(root, config, false);
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
