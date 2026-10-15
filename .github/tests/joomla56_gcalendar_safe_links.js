'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(
    path.join(__dirname, '../../modules/mod_sportsmanagement_gcalendar/js/gcalendar.js'),
    'utf8'
);

class Element {
    constructor(tag) {
        this.tag = tag;
        this.children = [];
        this.dataset = {};
        this.style = {};
        this.classList = {add() {}};
        this.hidden = false;
        this.textContent = '';
    }
    append(...elements) { this.children.push(...elements); }
    replaceChildren(...elements) { this.children = [...elements]; }
    addEventListener() {}
}

const grid = new Element('div');
const nodes = Object.fromEntries([
    '[data-calendar-grid]',
    '[data-calendar-weekdays]',
    '[data-calendar-title]',
    '[data-calendar-loading]',
].map(selector => [selector, selector === '[data-calendar-grid]' ? grid : new Element('div')]));

const root = new Element('div');
root.dataset.calendarOptionsKey = 'calendar.test';
root.querySelector = selector => nodes[selector] ?? null;
root.querySelectorAll = () => [];

const document = {
    readyState: 'complete',
    baseURI: 'https://sports.example/joomla/',
    createElement: tag => new Element(tag),
    querySelectorAll: selector => selector === '[data-jsm-gcalendar]' ? [root] : [],
};

const urls = [
    'javascript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    '//outside.example/steal',
    'https://outside.example/event',
    '/joomla/index.php?option=com_sportsmanagement&view=event',
    'https://sports.example/joomla/index.php?view=event',
];

const config = {
    feedUrl: '/joomla/index.php?view=jsonfeed',
    dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
    monthNamesShort: Array(12).fill('M'),
};

const events = urls.map((url, index) => ({
    title: 'Event ' + index, start: new Date().toISOString(), url, allDay: false,
}));

const sandbox = {
    document, URL, Date, Map, Number, String, Object, Array,
    Joomla: {getOptions: () => config},
    window: {Joomla: {getOptions: () => config}},
    fetch: async () => ({ok: true, json: async () => events}),
};

vm.runInNewContext(source, sandbox, {filename: 'gcalendar.js'});

const gather = element => [element, ...element.children.flatMap(gather)];
setTimeout(() => {
    const links = gather(grid).filter(node => node.tag === 'a');
    const byTitle = new Map(links.map(link => [link.textContent, link.href]));
    for (let index = 0; index < urls.length; index += 1) {
        const entry = [...byTitle.entries()].find(([title]) => title.includes('Event ' + index));
        assert.ok(entry, 'Calendar event ' + index + ' was not rendered');
        assert.equal(entry[1], index < 4 ? '#' : urls[index], 'Calendar URL validation at ' + index);
    }
    console.log('Joomla 5/6 calendar event link safety OK');
}, 20);
