(function () {
    'use strict';

    function getElement(id) {
        return document.getElementById(id);
    }

    function fieldValue(id) {
        const field = getElement(id);
        return field ? field.value : '';
    }

    function selectedText(id) {
        const field = getElement(id);

        if (!field || field.selectedIndex < 0) {
            return '';
        }

        return field.options[field.selectedIndex]?.text || '';
    }

    function ajaxResponse() {
        return getElement('ajaxresponse');
    }

    function setAjaxState(state, message) {
        const target = ajaxResponse();

        if (!target) {
            return;
        }

        target.classList.remove('ajax-loading', 'ajaxsuccess', 'ajaxerror');

        if (state) {
            target.classList.add(state);
        }

        target.textContent = message || '';
    }

    function startRequest() {
        setAjaxState('ajax-loading', '');
    }

    function resolveBaseUrl(baseUrl) {
        return baseUrl || window.baseajaxurl || 'index.php?option=com_sportsmanagement';
    }

    async function requestLegacyJson(action, params, baseUrl) {
        const url = new URL(resolveBaseUrl(baseUrl), window.location.href);
        url.searchParams.set('task', `matches.${action}`);
        url.searchParams.set('tmpl', 'component');

        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value == null ? '' : String(value));
        });

        const response = await fetch(url.toString(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const body = await response.text();

        try {
            const decoded = JSON.parse(body);
            return typeof decoded === 'string' ? decoded : String(decoded ?? '');
        } catch (error) {
            return body;
        }
    }

    function responseParts(response) {
        return String(response || '').split('&');
    }

    function handleRequestError(error) {
        console.error('SportsManagement edit-match request failed.', error);
        setAjaxState('ajaxerror', error instanceof Error ? error.message : String(error));
    }

    function insertBeforeNewRow(tableId, row) {
        const table = getElement(tableId);
        const body = table?.tBodies?.[0];

        if (!body) {
            return;
        }

        const newRow = Array.from(body.rows).find((candidate) => candidate.id === 'row-new');
        body.insertBefore(row, newRow || null);
    }

    function createCell(text, className) {
        const cell = document.createElement('td');
        cell.textContent = text || '';

        if (className) {
            cell.className = className;
        }

        return cell;
    }

    function createDeleteButton(id, handler) {
        const button = document.createElement('input');
        button.type = 'button';
        button.id = id;
        button.className = 'inputbox';
        button.value = window.str_delete || 'Delete';
        button.addEventListener('click', handler);
        return button;
    }

    function clearFields(ids) {
        ids.forEach((id) => {
            const field = getElement(id);

            if (field) {
                field.value = '';
            }
        });
    }

    function getPlayerSelect(index) {
        const select = document.createElement('select');
        select.id = 'teamplayer_id';
        select.className = 'inputbox span2';

        const rosters = Array.isArray(window.rosters) ? window.rosters : [];
        const roster = Array.isArray(rosters[index]) ? rosters[index] : [];

        roster.forEach((player) => {
            const option = document.createElement('option');
            option.value = player.value ?? '';
            option.textContent = player.text ?? '';
            select.appendChild(option);
        });

        return select;
    }

    function updatePlayerSelect() {
        const container = getElement('cell-player');
        const teamSelect = getElement('team_id');

        if (!container || !teamSelect) {
            return false;
        }

        container.replaceChildren(getPlayerSelect(teamSelect.selectedIndex));
        return true;
    }

    async function saveNewEvent(matchId, projectTime, baseUrl) {
        const eventSum = fieldValue('event_sum');

        if (eventSum === '') {
            return false;
        }

        startRequest();

        try {
            const response = await requestLegacyJson('saveevent', {
                teamplayer_id: fieldValue('teamplayer_id'),
                projectteam_id: fieldValue('team_id'),
                event_type_id: fieldValue('event_type_id'),
                event_time: fieldValue('event_time'),
                match_id: matchId ?? window.matchid ?? 0,
                projecttime: projectTime ?? window.projecttime ?? 0,
                useeventtime: window.useeventtime ?? 0,
                doubleevents: window.doubleevents ?? 0,
                event_sum: eventSum,
                notice: fieldValue('notice'),
            }, baseUrl);

            eventSaved(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function eventSaved(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            clearFields(['notice', 'event_time', 'event_sum']);
            return;
        }

        const eventId = parts[0];
        const row = document.createElement('tr');
        row.id = `rowevent-${eventId}`;
        row.appendChild(createCell(selectedText('team_id')));
        row.appendChild(createCell(selectedText('teamplayer_id')));
        row.appendChild(createCell(selectedText('event_type_id'), 'text-center'));
        row.appendChild(createCell(fieldValue('event_sum'), 'text-center'));
        row.appendChild(createCell(fieldValue('event_time'), 'text-center'));
        row.appendChild(createCell(fieldValue('notice')));

        const actionCell = createCell('', 'text-center');
        actionCell.appendChild(createDeleteButton(`deleteevent-${eventId}`, () => deleteEvent(eventId)));
        row.appendChild(actionCell);
        insertBeforeNewRow('table-event', row);

        setAjaxState('ajaxsuccess', parts[1] || '');
        clearFields(['notice', 'event_time', 'event_sum']);
    }

    async function deleteEvent(eventId, baseUrl) {
        startRequest();

        try {
            const response = await requestLegacyJson('removeEvent', {
                event_id: eventId,
            }, baseUrl);
            eventDeleted(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function eventDeleted(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            return;
        }

        getElement(`rowevent-${parts[2]}`)?.remove();
        setAjaxState('ajaxsuccess', parts[1] || '');
    }

    async function saveNewComment(matchId, projectTime, baseUrl) {
        startRequest();

        try {
            const response = await requestLegacyJson('savecomment', {
                type: fieldValue('ctype'),
                event_time: fieldValue('c_event_time'),
                matchid: matchId ?? window.matchid ?? 0,
                notes: fieldValue('notes'),
                projecttime: projectTime ?? window.projecttime ?? 0,
            }, baseUrl);
            commentSaved(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function commentSaved(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            return;
        }

        const commentId = parts[0];
        const row = document.createElement('tr');
        row.id = `rowcomment-${commentId}`;
        row.appendChild(createCell(selectedText('ctype')));
        row.appendChild(createCell(fieldValue('c_event_time'), 'text-center'));
        row.appendChild(createCell(fieldValue('notes')));

        const actionCell = createCell('', 'text-center');
        actionCell.appendChild(createDeleteButton(`deletecomment-${commentId}`, () => deleteCommentary(commentId)));
        row.appendChild(actionCell);

        const table = getElement('table-commentary');
        const body = table?.tBodies?.[0];

        if (body) {
            const newRow = getElement('rowcomment-new');
            body.insertBefore(row, newRow || null);
        }

        setAjaxState('ajaxsuccess', parts[1] || '');
        clearFields(['notes', 'c_event_time']);
    }

    async function deleteCommentary(commentaryId, baseUrl) {
        startRequest();

        try {
            const response = await requestLegacyJson('removeCommentary', {
                event_id: commentaryId,
            }, baseUrl);
            commentaryDeleted(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function commentaryDeleted(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            return;
        }

        getElement(`rowcomment-${parts[2]}`)?.remove();
        setAjaxState('ajaxsuccess', parts[1] || '');
    }

    async function saveNewSubstitution(matchId, teamId, projectTime, baseUrl) {
        startRequest();

        try {
            const response = await requestLegacyJson('savesubst', {
                in: fieldValue('in'),
                out: fieldValue('out'),
                project_position_id: fieldValue('project_position_id'),
                in_out_time: fieldValue('in_out_time'),
                teamid: teamId ?? window.teamid ?? 0,
                matchid: matchId ?? window.matchid ?? 0,
                projecttime: projectTime ?? window.projecttime ?? 0,
            }, baseUrl);
            substitutionSaved(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function substitutionSaved(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            return;
        }

        const substitutionId = parts[0];
        const row = document.createElement('tr');
        row.id = `sub-${substitutionId}`;
        row.appendChild(createCell(selectedText('out')));
        row.appendChild(createCell(selectedText('in')));
        row.appendChild(createCell(selectedText('project_position_id')));
        row.appendChild(createCell(fieldValue('in_out_time')));

        const actionCell = document.createElement('td');
        actionCell.appendChild(createDeleteButton(`deletesubst-${substitutionId}`, () => deleteSubstitution(substitutionId)));
        row.appendChild(actionCell);
        insertBeforeNewRow('table-substitutions', row);

        setAjaxState('ajaxsuccess', parts[1] || '');
        clearFields(['in_out_time']);

        ['in', 'out', 'project_position_id'].forEach((id) => {
            const select = getElement(id);

            if (select) {
                select.selectedIndex = 0;
            }
        });
    }

    async function deleteSubstitution(substitutionId, baseUrl) {
        startRequest();

        try {
            const response = await requestLegacyJson('removeSubst', {
                substid: substitutionId,
            }, baseUrl);
            substitutionDeleted(response);
        } catch (error) {
            handleRequestError(error);
        }

        return false;
    }

    function substitutionDeleted(response) {
        const parts = responseParts(response);

        if (parts[0] === '0' || parts[0] === '') {
            setAjaxState('ajaxerror', parts[1] || '');
            return;
        }

        getElement(`sub-${parts[2]}`)?.remove();
        setAjaxState('ajaxsuccess', parts[1] || '');
    }

    window.updatePlayerSelect = updatePlayerSelect;
    window.getPlayerSelect = getPlayerSelect;
    window.save_new_event = saveNewEvent;
    window.eventsaved = eventSaved;
    window.deleteevent = deleteEvent;
    window.eventdeleted = eventDeleted;
    window.save_new_comment = saveNewComment;
    window.commentsaved = commentSaved;
    window.deletecommentary = deleteCommentary;
    window.button_delete_commentary = deleteCommentary;
    window.commentarydeleted = commentaryDeleted;
    window.save_new_subst = saveNewSubstitution;
    window.substsaved = substitutionSaved;
    window.deletesubst = deleteSubstitution;
    window.delete_subst = deleteSubstitution;
    window.substdeleted = substitutionDeleted;

    document.addEventListener('DOMContentLoaded', () => {
        const saveEventButton = getElement('save-new-event');

        if (saveEventButton && !saveEventButton.hasAttribute('onclick')) {
            saveEventButton.addEventListener('click', () => saveNewEvent());
        }
    });
}());