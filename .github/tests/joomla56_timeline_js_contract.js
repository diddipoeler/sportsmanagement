/**
 * Joomla 5/6 regression: timeline AJAX clients include real CSRF and POST data.
 * Runs on Node without browser dependencies.
 */
'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const token = 'abcdef0123456789abcdef0123456789';
const form = {
    querySelectorAll: () => [{name: token, value: '1'}],
};

function checkPayload(request, task, values) {
    assert.equal(request.type || request.method, 'POST');
    const url = new URL(request.url);
    assert.equal(url.searchParams.get('task'), 'matches.' + task);
    assert.equal(url.searchParams.get('event_id'), null);
    assert.equal(url.searchParams.get('match_id'), null);
    assert.equal(url.searchParams.get('notes'), null);
    assert.equal(url.searchParams.get('substid'), null);
    const body = new URLSearchParams(request.data || request.body);
    assert.equal(body.get(token), '1', 'Session token must be in POST data');

    for (const [key, expected] of Object.entries(values)) {
        assert.equal(body.get(key), String(expected), key + ' must be in POST data');
    }
}

{
    const sent = [];
    const document = {querySelector: () => form};
    const chain = {
        html() {return this;},
        addClass() {return this;},
        removeClass() {return this;},
        val() {return '5';},
        text() {return '5';},
        ready() {return this;},
    };
    function jquery() {return chain;}
    jquery.ajax = (options) => sent.push({
        url: options.url,
        type: options.type,
        data: options.data,
    });
    const context = {
        window: {Joomla: {getOptions: () => token}},
        document, jQuery: jquery, URLSearchParams, console,
        baseajaxurl: 'https://example.org/administrator/index.php?option=com_sportsmanagement',
        matchid: 8, teamid: 2, useeventtime: 0, doubleevents: 0, projecttime: 90,
    };
    vm.runInNewContext(
        fs.readFileSync('admin/assets/js/diddioeler.js', 'utf8'),
        context,
        {filename: 'diddioeler.js'}
    );

    context.save_new_event();
    context.deleteevent(19);
    context.save_new_comment();
    context.deletecommentary(29);
    context.save_new_subst();
    context.deletesubst(39);

    assert.equal(sent.length, 6, 'All administrator timeline tasks should send AJAX');
    [
        ['saveevent', {event_sum: 5, match_id: 8}],
        ['removeEvent', {event_id: 19}],
        ['savecomment', {matchid: 8, notes: 5}],
        ['removeCommentary', {event_id: 29}],
        ['savesubst', {matchid: 8, project_position_id: 5}],
        ['removeSubst', {substid: 39}],
    ].forEach(([task, values], index) => checkPayload(sent[index], task, values));

    // A missing token must stop a mutation before making a request.
    document.querySelector = () => ({querySelectorAll: () => []});
    assert.throws(() => context.deleteevent(100), /CSRF form token is missing/);
    assert.equal(sent.length, 6);
}

async function checkFrontend(file) {
    const sent = [];
    const document = {
        readyState: 'loading',
        addEventListener() {},
        querySelector: () => form,
        getElementById(id) {
            if (id === 'ajaxresponse') {
                return null;
            }

            return {value: id === 'event_sum' ? '4' : '7'};
        },
    };
    const window = {
        Joomla: {getOptions: () => token},
        location: {href: 'https://example.org/index.php?option=com_sportsmanagement'},
    };
    const context = {
        window, document, URL, URLSearchParams,
        console, HTMLSelectElement: class {},
        HTMLInputElement: class {},
        fetch: async (url, options) => {
            sent.push({url, ...options});
            return {ok: true, text: async () => JSON.stringify('0&Handled error')};
        },
    };
    vm.runInNewContext(fs.readFileSync(file, 'utf8'), context, {filename: file});
    await window.save_new_event(8, 90);
    await window.deleteevent(19);
    assert.equal(sent.length, 2, file + ' must send both writes');

    checkPayload(sent[0], 'saveevent', {event_sum: 4, match_id: 8});
    checkPayload(sent[1], 'removeEvent', {event_id: 19});

    document.querySelector = () => ({querySelectorAll: () => []});
    await window.deleteevent(20);
    assert.equal(sent.length, 2, 'Frontend must never send without a CSRF token');
}

(async () => {
    await checkFrontend('site/assets/js/editmatch-editing.js');
    await checkFrontend('site/assets/js/editmatch.js');
    console.log('Joomla 5/6 timeline JavaScript POST and CSRF: OK');
})().catch((error) => {console.error(error); process.exitCode = 1;});
