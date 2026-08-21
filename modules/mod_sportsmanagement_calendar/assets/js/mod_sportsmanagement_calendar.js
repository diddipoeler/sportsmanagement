window.jlcinjectcontainer = window.jlcinjectcontainer || {};
window.jlcmodal = window.jlcmodal || {};

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

function jlCalmod_injectContent(sourceId, destinationId, moduleId) {
    const source = document.getElementById(sourceId);
    const modal = document.getElementById('myModal' + moduleId);
    const modalBody = document.getElementById('myModalbody' + moduleId);

    if (!source || !modal || !modalBody) {
        return;
    }

    modalBody.innerHTML = source.innerHTML;

    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
        return;
    }

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
        jlCalmod_injectContent(
            temporaryContentId,
            window.jlcinjectcontainer[moduleId] || '',
            moduleId
        );
    }
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
    const calendar = document.getElementById('jlccalendar-' + moduleId);

    if (!calendar) {
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
    image.src = (window.calendar_baseurl || '')
        + 'modules/mod_sportsmanagement_calendar/assets/images/loading.gif';
    image.alt = '';
    loading.appendChild(image);
    calendar.appendChild(loading);

    jlcHide(moduleId);

    const body = new URLSearchParams({
        jlcteam: String(teamId),
        year: String(year),
        month: String(month),
        day: String(day),
        ajaxCalMod: '1',
        ajaxmodid: String(moduleId),
    });

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
            credentials: 'same-origin',
            body: body.toString(),
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const html = await response.text();
        const startMarker = '<!--jlccalendar-' + moduleId + ' start-->';
        const endMarker = '<!--jlccalendar-' + moduleId + ' end-->';
        const start = html.indexOf(startMarker);
        const end = html.indexOf(endMarker);

        if (start === -1 || end === -1 || end <= start) {
            throw new Error('Calendar fragment not found in response');
        }

        calendar.innerHTML = html.substring(start, end);
    } catch (error) {
        const loader = document.getElementById('loadingDiv-' + moduleId);
        if (loader) {
            loader.remove();
        }

        console.error('SportsManagement calendar update failed:', error);
    }
}
