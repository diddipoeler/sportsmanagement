// Exercise the real navigation option-update functions with native and simulated Choices selects.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class Option {
    constructor(text = '', value = '', defaultSelected = false, selected = false) {
        Object.assign(this, {textContent: text, value, selected});
    }
    cloneNode() { return new Option(this.textContent, this.value, false, this.selected); }
}
function fragment() {
    return {children: [], appendChild(option) { this.children.push(option); }};
}
function load(file, names) {
    const source = fs.readFileSync(file, 'utf8');
    const end = source.lastIndexOf('})();');
    assert.ok(end > 0);
    const context = {
        document: {readyState:'complete', querySelectorAll:()=>[],
            createDocumentFragment:fragment, createElement:()=>new Option()},
        Option, Event: class { constructor(type) { this.type = type; } },
        CSS: {escape: value => value},
    };
    vm.runInNewContext(source.slice(0,end) + 'globalThis.testApi = {' + names.join(',') + '};\n' + source.slice(end), context);
    return context.testApi;
}
function fixture(enhanced) {
    const calls = [];
    const select = {
        options:[new Option('Old project','9',false,true)],
        jsmPlaceholder:new Option('Select project','0'),
        events:[],
        replaceChildren(nodes) { this.options = nodes ? Array.from(nodes.children) : []; },
        dispatchEvent(event) { this.events.push(event.type); },
        closest() { return enhanced ? {choicesInstance:choices} : null; },
    };
    const choices = {
        clearStore() { calls.push('clear'); select.options = []; },
        setChoices(rows, value, label, replace) {
            assert.equal(value,'value'); assert.equal(label,'label'); assert.equal(replace,true);
            calls.push('set');
            select.options = Array.from(rows, row => new Option(row.label,row.value,false,row.selected));
        },
    };
    return {select,calls};
}
const normal = load('modules/mod_sportsmanagement_navigation_menu/js/mod_sportsmanagement_navigation_menu.js',['updateProjects']);
const top = load('modules/mod_sportsmanagement_ajax_top_navigation_menu/js/native.js',['replaceOptions','clear','projectNavigation']);
const values = select => select.options.map(option => option.value);
for (const enhanced of [false,true]) {
    const {select,calls} = fixture(enhanced);
    const form = {elements:{p:select,include_season:{value:'1'}}};
    normal.updateProjects(form,{data:[{value:3,text:'League',season_name:'2025'},{value:3,text:'Duplicate'},{value:0,text:'Placeholder'}]});
    assert.deepEqual(values(select),['0','3']);
    assert.equal(select.options[1].textContent,'2025 - League');
    normal.updateProjects(form,{data:[{value:{bad:1},text:'Invalid'}]});
    assert.deepEqual(values(select),['0','3']);
    normal.updateProjects(form,{success:false,data:[]});
    assert.deepEqual(values(select),['0','3']);
    normal.updateProjects(form,{data:[]});
    assert.deepEqual(values(select),['0']);
    assert.deepEqual(calls,enhanced?['clear','set','clear','set']:[]);

    const other = fixture(enhanced);
    top.replaceOptions(other.select,{data:{data:[{id:7,name:'Cup',project_type:'TOURNAMENT_MODE'},{id:7,name:'Duplicate'},{id:8,name:'League'}]}});
    assert.deepEqual(values(other.select),['7','8']);
    other.select.value = '7';
    assert.equal(other.select.jsmProjectTypes.get('7'),'TOURNAMENT_MODE');
    assert.ok(top.projectNavigation({navpoint:[],navpointLabel:[]},other.select).views.includes('tournamentbracket'));
    top.replaceOptions(other.select,{data:{unexpected:1}});
    top.replaceOptions(other.select,{data:[{id:{bad:1}}]});
    assert.deepEqual(values(other.select),['7','8']);
    top.clear({querySelector:()=>other.select},['project']);
    assert.deepEqual(values(other.select),[]);
    assert.equal(other.select.jsmProjectTypes.size,0);
    assert.deepEqual(other.calls,enhanced?['clear','set','clear','set']:[]);
    assert.deepEqual(other.select.events,['change:options','change:options']);
}
console.log('Navigation search regressions passed (native and simulated Choices).');
