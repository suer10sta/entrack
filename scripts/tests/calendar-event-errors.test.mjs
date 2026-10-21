import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../resources/views/admin/calendar/calendar.blade.php', import.meta.url), 'utf8');
const helpers = source.slice(source.indexOf('            function clearEventErrors()'), source.indexOf('            // Function to show modal'));
const handler = source.slice(source.indexOf("            $('#saveEvent').click(function ()"), source.indexOf('            // Handle event deletion'));
const script = (helpers + handler)
    .replace(/@js\(__\('validation.after_or_equal',[^\n]*?\]\)\)/g, '"end_before_start"')
    .replace(/@js\(__\('([^']*)'\)\)/g, (_, key) => JSON.stringify(key.replace(/^messages\./, '')));

function setup(values = {}) {
    const nodes = new Map(), requests = [];
    let save;
    function node(id) {
        if (!nodes.has(id)) nodes.set(id, {name: id, value: '', disabled: false, attrs: {}, messages: [], hidden: true});
        return nodes.get(id);
    }
    const fields = ['title', 'start', 'end', 'description', 'client_id', 'project_id', 'task_id'].map(node);
    Object.assign(node('title'), {value: 'Project meeting'});
    Object.assign(node('start'), {value: '2026-10-21T10:00'});
    Object.assign(node('end'), {value: '2026-10-21T11:00'});
    for (const [key, value] of Object.entries(values)) node(key).value = value;
    function wrap(items) {
        const first = items[0];
        const api = {0: first,
            val() {return first.value;},
            prop(key, value) {items.forEach(item => item[key] = value); return api;},
            attr(key, value) {items.forEach(item => Object.assign(item.attrs, typeof key === 'object' ? key : {[key]: value})); return api;},
            removeAttr(keys) {items.forEach(item => keys.split(' ').forEach(key => delete item.attrs[key])); return api;},
            addClass() {return api;}, removeClass() {return api;}, trigger() {return api;},
            filter(callback) {return wrap(items.filter(item => callback.call(item)));},
            empty() {first.messages = []; return api;},
            text(value) {first.text = value; return api;},
            appendTo(list) {list[0].messages.push(first.text); return api;},
            is() {return false;}, spectrum() {return {toHexString: () => '#123456'};},
            map() {return {get: () => []};}, data() {return undefined;},
            click(callback) {save = callback; return api;}};
        return api;
    }
    function $(selector) {
        if (typeof selector === 'object') return wrap([selector]);
        if (selector === '<li>') return wrap([{attrs: {}}]);
        if (selector === '#eventForm :input' || selector.includes('aria-invalid')) return wrap(fields);
        return wrap([node(selector.replace(/^#/, ''))]);
    }
    $.ajax = request => requests.push(request);
    function moment(value) {
        const date = new Date(value);
        return {isValid: () => Number.isFinite(date.getTime()),
            isBefore: other => date.getTime() < other.timestamp,
            timestamp: date.getTime(), format: () => value ? value.replace('T', ' ') + ':00' : 'Invalid date'};
    }
    moment.ISO_8601 = 'ISO';
    vm.runInNewContext(script, {$, moment, calendarRoutePrefix: '/admin/calendar', defaultEventColor: '#123456'});
    return {node, requests, click: () => save.call(node('saveEvent'))};
}

test('missing title and dates explain all required corrections without making a request', () => {
    const ui = setup({title: '  ', start: '', end: ''}); ui.click();
    assert.equal(ui.requests.length, 0);
    assert.deepEqual(Array.from(ui.node('eventFormErrorMessages').messages), ['Please enter a title.', 'calendar_time_required']);
    assert.equal(ui.node('eventFormErrors').hidden, false);
    assert.equal(ui.node('title').attrs['aria-invalid'], 'true');
    assert.equal(ui.node('end').attrs['aria-invalid'], 'true');
});

test('invalid dates and reversed ranges are explained before submission', () => {
    for (const [values, expected] of [[{start: 'invalid'}, 'calendar_invalid_start'], [{end: 'invalid'}, 'calendar_invalid_end'], [{end: '2026-10-21T09:00'}, 'end_before_start']]) {
        const ui = setup(values); ui.click();
        assert.equal(ui.requests.length, 0);
        assert(ui.node('eventFormErrorMessages').messages.includes(expected));
    }
});

test('existing one-date and equal-date event behavior is preserved', () => {
    for (const values of [{start: ''}, {end: ''}, {end: '2026-10-21T10:00'}]) {
        const ui = setup(values); ui.click();
        assert.equal(ui.requests.length, 1);
        assert.equal(ui.requests[0].headers.Accept, 'application/json');
    }
});

test('server validation errors are displayed and fields highlighted without losing input', () => {
    const ui = setup({description: 'Original description'}); ui.click();
    ui.requests[0].error({status: 422, responseJSON: {errors: {project_id: ['This project is unavailable.'], task_id: ['The task does not belong to this project.']}}});
    assert.equal(ui.node('project_id').attrs['aria-invalid'], 'true');
    assert.equal(ui.node('eventFormErrorMessages').messages.length, 2);
    assert.equal(ui.node('description').value, 'Original description');
    ui.requests[0].complete();
    assert.equal(ui.node('saveEvent').disabled, false);
});

test('network, session, permissions and server failures get distinct actionable messages', () => {
    for (const [status, expected] of [[0, 'calendar_save_network_error'], [401, 'calendar_save_session_expired'], [419, 'calendar_save_session_expired'], [403, 'calendar_save_forbidden'], [500, 'calendar_save_unavailable']]) {
        const ui = setup(); ui.click();
        ui.click(); assert.equal(ui.requests.length, 1);
        ui.requests[0].error({status, responseJSON: {message: 'Technical details should not be displayed'}});
        assert.deepEqual(Array.from(ui.node('eventFormErrorMessages').messages), [expected]);
        ui.requests[0].complete();
        assert.equal(ui.node('saveEvent').disabled, false);
    }
});
