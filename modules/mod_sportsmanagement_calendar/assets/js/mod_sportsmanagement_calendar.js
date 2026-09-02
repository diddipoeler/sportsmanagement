function jsmCalendarRoot(moduleId) {
    return document.getElementById('jlccalendar-' + moduleId);
}

function jlCalmod_setTitle(targetId, sourceId, title, moduleId) {
    const titleId = sourceId.replace('jlcal_', 'jlcaltitte_');
    const source = document.getElementById(titleId);
    const target = document.getElementById('jlCalListDayTitle-' + moduleId);

    if (source && target) {
        target.innerHTML = source.innerHTML;
    }
}

function jlCalmod_setContent(targetId, temporaryContentId, sourceContent) {
    const target = document.getElementById(targetId);
    const temporary = document.getElementById(temporaryContentId);

    if (target) {
        target.innerHTML = sourceContent;
    }

    if (temporary) {
        temporary.innerHTML = '<div class="componentheading"></div>' + sourceContent;
    }
}

function jlCalmod_injectContent(sourceId, moduleId) {
    const source = document.getElementById(sourceId);
    const modal = document.getElementById('myModal' + moduleId);
    const modalBody = document.getElementById('myModalbody' + moduleId);
    const root = jsmCalendarRoot(moduleId);

    if (!source || !modal || !modalBody) {
        return;
    }

    modalBody.innerHTML = source.innerHTML;

    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
        return;
    }

    const destinationId = root?.dataset.injectContainer || '';
    const destination = destinationId
        ? document.getElementById(destinationId.replace(/^#/, ''))
        : null;

    if (destination) {
        destination.innerHTML = source.innerHTML;
    }
}

function jlCalmod_showhide(targetId, sourceId, title, inject, moduleId) {
    const target = document.getElementById(targetId);
    const source = document.getElementById(sourceId);

    if (!target) {
        return;
    }

    const sourceContent = source ? source.innerHTML : 'Something went wrong this day';
    const temporaryContentId = 'jlCalList-' + moduleId + '_temp';
    const modalHeader = document.getElementById('myModalheader' + moduleId);

    if (modalHeader) {
        modalHeader.textContent = title;
    }

    jlCalmod_setTitle(targetId, sourceId, title, moduleId);
    jlCalmod_setContent(targetId, temporaryContentId, sourceContent);

    if (Number(inject) > 0) {
        jlCalmod_injectContent(temporaryContentId, moduleId);
    }
}

function jsmCalendarOpenDay(link) {
    const moduleId = Number(link.dataset.moduleId || 0);
    const sourceId = link.dataset.sourceId || '';
    const root = jsmCalendarRoot(moduleId);

    if (!moduleId || !sourceId || !root) {
        return;
    }

    const titleSource = document.getElementById(sourceId.replace('jlcal_', 'jlcaltitte_'));
    const title = titleSource?.textContent?.trim() || link.title || '';
    const inject = root.dataset.injectContainer ? 1 : 0;

    jlCalmod_showhide(
        'jlCalList-' + moduleId,
        sourceId,
        title,
        inject,
        moduleId
    );
}

function jlcHide(moduleId) {
    const dayTitle = document.getElementById('jlCalListDayTitle-' + moduleId);
    const listTitle = document.getElementById('jlCalListTitle-' + moduleId);
    const teamSelect = document.getElementById('jlcteam' + moduleId);
    const list = document.getElementById('jlCalList-' + moduleId);

    if (dayTitle) {
        dayTitle.innerHTML = '';
    }
    if (listTitle) {
        listTitle.innerHTML = '';
    }
    if (teamSelect) {
        teamSelect.classList.toggle('jcalbox_hidden');
    }
    if (list) {
        list.innerHTML = '';
    }
}

async function jlcnewDate(month, year, moduleId, day = 0) {
    const teamSelect = document.getElementById('jlcteam' + moduleId);
    const teamId = teamSelect ? Number(teamSelect.value || 0) : 0;
    const calendar = jsmCalendarRoot(moduleId);

    if (!calendar || !calendar.dataset.refreshUrl) {
        return;
    }

    month = Number(month);
    year = Number(year);
    day = Number(day || 0);

    if (month <= 0) {
        month += 12;
        year--;
    } else if (month > 12) {
        month -= 12;
        year++;
    }

    const loading = document.createElement('p');
    loading.id = 'loadingDiv-' + moduleId;
    loading.className = 'jsm-calendar-loading';

    const image = document.createElement('img');
    image.src = new URL(
        'modules/mod_sportsmanagement_calendar/assets/images/loading.gif',
        document.baseURI
    ).href;
    image.alt = '';
    loading.appendChild(image);
    calendar.appendChild(loading);

    jlcHide(moduleId);

    const requestUrl = new URL(calendar.dataset.refreshUrl, document.baseURI);
    const pageUrl = new URL(window.location.href);
    requestUrl.searchParams.set('module_id', String(moduleId));
    requestUrl.searchParams.set('year', String(year));
    requestUrl.searchParams.set('month', String(month));
    requestUrl.searchParams.set('day', String(day));
    requestUrl.searchParams.set('jlcteam', String(teamId));
    requestUrl.searchParams.set('ajaxCalMod', '1');
    requestUrl.searchParams.set('ajaxmodid', String(moduleId));

    ['Itemid', 'lang'].forEach((name) => {
        const value = pageUrl.searchParams.get(name);
        if (value) {
            requestUrl.searchParams.set(name, value);
        }
    });

    try {
        const response = await fetch(requestUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {'Accept': 'text/html'},
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const html = await response.text();

        if (!html.trim()) {
            throw new Error('Calendar fragment is empty');
        }

        calendar.innerHTML = html;
        calendar.dataset.calendarMonth = String(month);
        calendar.dataset.calendarYear = String(year);
    } catch (error) {
        const loader = document.getElementById('loadingDiv-' + moduleId);
        if (loader) {
            loader.remove();
        }

        console.error('SportsManagement calendar update failed:', error);
    }
}

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const navigation = event.target.closest('[data-jsm-calendar-nav]');

    if (navigation) {
        const moduleId = Number(navigation.dataset.moduleId || 0);
        const month = Number(navigation.dataset.calendarMonth || 0);
        const year = Number(navigation.dataset.calendarYear || 0);

        if (moduleId && month && year) {
            event.preventDefault();
            jlcnewDate(month, year, moduleId);
        }

        return;
    }

    const dayLink = event.target.closest('[data-jsm-calendar-day]');

    if (!dayLink) {
        return;
    }

    event.preventDefault();
    jsmCalendarOpenDay(dayLink);
});

document.addEventListener('change', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const select = event.target.closest('[data-jsm-calendar-team]');

    if (!select) {
        return;
    }

    const moduleId = Number(select.dataset.jsmCalendarTeam || 0);
    const root = jsmCalendarRoot(moduleId);

    if (!moduleId || !root) {
        return;
    }

    jlcnewDate(
        Number(root.dataset.calendarMonth || 0),
        Number(root.dataset.calendarYear || 0),
        moduleId
    );
});

const initialiseCalendarDay = () => {
    document.querySelectorAll('[data-jsm-calendar-autoopen="1"]').forEach((link) => {
        jsmCalendarOpenDay(link);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseCalendarDay, {once: true});
} else {
    initialiseCalendarDay();
}
