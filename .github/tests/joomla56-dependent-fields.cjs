// Regression tests for the actual inline JavaScript in the native fields.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function harness(field, multi, enhanced = false) {
    const source = fs.readFileSync('admin/src/Field/' + field + '.php', 'utf8');
    const script = source.match(/<<<'JS'\r?\n([\s\S]*?)\r?\nJS;/)[1];
    const pending = [];
    const listeners = {};
    const dependency = {
        value: '1', matches: () => true,
        getAttribute: () => 'project',
        hasAttribute: () => false, setAttribute() {},
        addEventListener(type, cb) { listeners.dependency = cb; }
    };
    const hidden = {value: '1|2'};
    class Select {
        constructor() { this.options = []; this.multiple = multi; }
        get selectedOptions() { return this.options.filter(o => o.selected); }
        add(o) { this.options.push(o); }
        remove(i) { this.options.splice(i, 1); }
        replaceChildren() { this.options = []; }
        addEventListener(type, cb) { listeners.selection = cb; }
        dispatchEvent() {}
    }
    const select = new Select();
    const calls = [];
    const choices = {
        clearStore() {
            calls.push('clearStore');
            select.replaceChildren();
        },
        setChoices(options, valueKey, labelKey, replace) {
            assert.equal(valueKey, 'value');
            assert.equal(labelKey, 'label');
            assert.equal(replace, true);
            calls.push('setChoices');
            for (const option of options) {
                select.add({value: option.value, text: option.label, selected: option.selected});
            }
        }
    };
    select.closest = selector => enhanced && selector === 'joomla-field-fancy-select'
        ? {choicesInstance: choices} : null;
    const config = multi
        ? {visibleId:'list', hiddenId:'hidden', task:'teams', depends:['project'], selected:['1','2']}
        : {targetId:'list', task:'teams', depends:'project', group:'', view:'', database:'0', slug:''};
    const context = {
        document: {
            readyState:'complete',
            getElementById: id => ({list:select, hidden, project:dependency}[id] || null),
            querySelectorAll: () => [],
        },
        window: {console},
        HTMLSelectElement: Select,
        Option: function(text,value,defaultSelected,selected) {
            Object.assign(this,{text,value,selected:!!selected});
        },
        Event: function(){},
        URLSearchParams,
        fetch: () => new Promise(resolve => pending.push(resolve))
    };
    vm.runInNewContext(script.replace('__CONFIG__', JSON.stringify(config)), context);
    return {select, hidden, listeners, pending, calls};
}

function respond(resolve, data) {
    resolve({ok:true,json:async()=>data});
}

async function main() {
    const rows = [{value:'1',text:'One'},{value:'2',text:'Two'}];
    for (const enhanced of [false, true]) {
    const multi = harness('MultidependsqlField', true, enhanced);
    assert.equal(multi.hidden.value, '1|2', 'Initial pending request must not erase saved values');
    const initial = multi.pending.shift();
    const latest = multi.listeners.dependency();
    respond(multi.pending.shift(), {data:rows});
    await latest;
    respond(initial, {data:[{value:'9',text:'Stale'}]});
    await new Promise(resolve=>setImmediate(resolve));
    assert.deepEqual(multi.select.options.map(o=>o.value), ['1','2']);
    assert.equal(multi.hidden.value, '1|2');
    multi.select.options.forEach(o=>o.selected=false);
    multi.listeners.selection();
    const refresh = multi.listeners.dependency();
    respond(multi.pending.shift(), {data:rows});
    await refresh;
    assert.equal(multi.hidden.value,'', 'Cleared selections must not be restored');
    const failure = multi.listeners.dependency();
    respond(multi.pending.shift(), {success:false,data:[]});
    await failure;
    assert.equal(multi.select.options.length,2);

    const single = harness('DependsqlField', false, enhanced);
    const old = single.listeners.dependency();
    const current = single.listeners.dependency();
    respond(single.pending.pop(), {data:rows});
    await current;
    respond(single.pending.shift(), {data:[{value:'9',text:'Stale'}]});
    await old;
    assert.deepEqual(single.select.options.map(o=>o.value), ['1','2']);
    // A malformed response must leave the current options and selection intact.
    for (const fixture of [single, multi]) {
        const before = fixture.select.options.map(o => o.value);
        const invalid = fixture.listeners.dependency();
        respond(fixture.pending.shift(), {data:{unexpected:true}});
        await invalid;
        assert.deepEqual(fixture.select.options.map(o => o.value), before);
        const malformed = fixture.listeners.dependency();
        respond(fixture.pending.shift(), {data:[{value:{bad:true},text:'Invalid'}]});
        await malformed;
        assert.deepEqual(fixture.select.options.map(o => o.value), before);
        const nested = fixture.listeners.dependency();
        respond(fixture.pending.shift(), {data:{data:[{value:'2',text:'Two'},{value:'2',text:'Duplicate'},{value:'3',text:'Three'}]}});
        await nested;
        assert.deepEqual(fixture.select.options.map(o => o.value), ['2','3']);
        if (enhanced) {
            assert.ok(fixture.calls.length >= 4);
            assert.equal(fixture.calls.length % 2, 0);
            for (let i = 0; i < fixture.calls.length; i += 2) {
                assert.deepEqual(fixture.calls.slice(i,i+2), ['clearStore','setChoices']);
            }
        } else {
            assert.equal(fixture.calls.length, 0);
        }
    }
    assert.equal(single.select.selectedOptions[0].value, '2');
    assert.equal(multi.hidden.value, '');
    const empty = multi.listeners.dependency();
    respond(multi.pending.shift(), {data:[]});
    await empty;
    assert.equal(multi.select.options.length, 0);
    assert.equal(multi.hidden.value, '');
    }
    console.log('Dependent field AJAX regressions passed (native and simulated Choices).');
}
main().catch(error=>{console.error(error);process.exitCode=1;});
