import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../resources/views/components/dashboard/navbar.blade.php', import.meta.url), 'utf8');
const start = source.indexOf('    function navbarChrono()');
const script = source.slice(start, source.indexOf('</script>', start))
    .replace(/@json\(__\('[^']*'\)\)/g, '"message"')
    .replace('@json(timezone_identifiers_list())', '["UTC"]')
    .replace(/@json\([^\n]+?\)/g, 'null')
    .replace(/@if[^\n]*/g, '').replace(/@endif|@else/g, '');

function harness(clockSkew = 0) {
    let now = Date.parse('2026-10-21T10:00:23Z') + clockSkew;
    let nextId = 0;
    const ticks = new Map();
    class Clock extends Date { static now() { return now; } }
    const context = {Date: Clock, Intl, console, window: {},
        setInterval(callback) { const id = ++nextId; ticks.set(id, callback); return id; },
        clearInterval(id) { ticks.delete(id); }};
    vm.createContext(context);
    vm.runInContext(script + ';this.state = navbarChrono();', context);
    return {state: context.state, ticks, advance(ms) { now += ms; for (const callback of ticks.values()) callback(); }};
}
const timer = overrides => ({task_employee_id: 7, employee_id: 2, task_id: 3, task_name: 'Task',
    start_date: '2026-10-21 10:00:00', started_at_ms: Date.parse('2026-10-21T10:00:00Z'),
    elapsed_seconds: 23, paused: '0', duration: '0', ...overrides});

test('server elapsed avoids both a one-hour head start and a future-date zero clamp', () => {
    for (const skew of [-3600000, 0, 3600000, 7200000]) {
        const {state, advance} = harness(skew);
        state.applyTimers([timer({elapsed_seconds: 0})]);
        assert.equal(state.elapsed, '00:00:00');
        advance(23000);
        assert.equal(state.elapsed, '00:00:23');
        assert.equal(state.runningTimers[0].elapsed, '00:00:23');
    }
});

test('text zero pause flag never resets a running timer to zero on polling', () => {
    for (const paused of ['0', 0, false, null]) {
        const {state, advance, ticks} = harness();
        state.applyTimers([timer({paused})]);
        assert.equal(state.elapsed, '00:00:23');
        advance(30000);
        assert.equal(state.elapsed, '00:00:53');
        state.applyTimers([timer({paused, elapsed_seconds: 53})]);
        assert.equal(state.elapsed, '00:00:53');
        assert.equal(ticks.size, 1);
        advance(1000);
        assert.equal(state.elapsed, '00:00:54');
    }
});

test('legacy payload also distinguishes text zero from pause and handles invalid dates', () => {
    const {state} = harness();
    assert.equal(state.timerBase(timer({elapsed_seconds: null})).offsetSec, 23);
    assert.equal(state.timerBase(timer({elapsed_seconds: null, paused: '1', duration: '2.5'})).offsetSec, 150);
    assert.equal(state.timerBase({start_date: 'invalid', paused: '0'}).offsetSec, 0);
    assert.equal(state.fmt(NaN), '00:00:00');
});

test('paused primary stays frozen while other running timers keep ticking', () => {
    const {state, advance} = harness();
    state.applyTimers([timer({task_employee_id: 8, started_at_ms: Date.parse('2026-10-21T10:00:10Z'), elapsed_seconds: 13}),
        timer({paused: '1', elapsed_seconds: 150})]);
    advance(10000);
    assert.equal(state.elapsed, '00:02:30');
    assert.equal(state.runningTimers[0].elapsed, '00:02:30');
    assert.equal(state.runningTimers[1].elapsed, '00:00:23');
});

test('resume starts from saved elapsed time and stopping clears the ticker', () => {
    const {state, advance, ticks} = harness();
    state.applyTimers([timer({paused: '1', elapsed_seconds: 150})]);
    state.applyTimers([timer({paused: '0', elapsed_seconds: 155})]);
    advance(2000);
    assert.equal(state.elapsed, '00:02:37');
    state.applyTimers([]);
    assert.equal(state.elapsed, '00:00:00');
    assert.equal(state.isRunning, false);
    assert.equal(ticks.size, 0);
});
