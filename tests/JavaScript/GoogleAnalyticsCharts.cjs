// Run with: node tests/JavaScript/GoogleAnalyticsCharts.cjs
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/manager-analytics.js'), 'utf8');
let plotWidth = 500;
class Element {
    constructor(tag) { this.tag = tag; this.children = []; this.attrs = {}; this.style = {}; this.handlers = {}; this.clientWidth = plotWidth; }
    append(...items) { this.children.push(...items); }
    insertBefore(item, before) { this.children.splice(this.children.indexOf(before), 0, item); }
    replaceChildren(...items) { this.children = items; }
    setAttribute(key, value) { this.attrs[key] = String(value); }
    addEventListener(event, fn) { this.handlers[event] = fn; }
    createTHead() { const n = new Element('thead'); this.append(n); return n; }
    createTBody() { const n = new Element('tbody'); this.append(n); return n; }
    insertRow() { const n = new Element('tr'); this.append(n); return n; }
    insertCell() { const n = new Element('td'); this.append(n); return n; }
}
const context = {
    Intl, Date, observers: new Map(), number: n => String(n),
    document: { createElement: tag => new Element(tag), createElementNS: (_, tag) => new Element(tag) },
    ResizeObserver: class { observe() {} disconnect() {} },
};
vm.createContext(context);
vm.runInContext(source.slice(source.indexOf('    function dailySeries('), source.indexOf('    const definitions =')), context);
const meta = { timezone: 'Europe/Brussels', fetched_at: '2026-10-26T15:00:00Z' };
const sample = { date: '20261026', activeUsers: 1, sessions: 1, screenPageViews: 2 };
const walk = n => [n, ...n.children.flatMap(walk)];
for (const days of [7, 28, 90, 180]) {
    const rows = context.dailySeries([sample], days, meta);
    assert.equal(rows.length, days + 1, 'Both existing API date bounds must be included');
    assert.equal(rows.at(-1).date, '20261026');
    assert.equal(rows.at(-1).screenPageViews, 2);
    assert.equal(rows.filter(r => r.activeUsers > 0).length, 1);
    assert.equal(rows.filter(r => r.missing).length, days);
    assert.equal(rows.reduce((sum, r) => sum + r.screenPageViews, 0), 2);
    assert.equal((Date.parse('2026-10-26') - Date.parse(`${rows[0].date.slice(0,4)}-${rows[0].date.slice(4,6)}-${rows[0].date.slice(6,8)}`)) / 86400000, days);
    for (const width of [280, 500, 900]) {
        plotWidth = width;
        for (const series of [[['activeUsers', 'Utilisateurs actifs', '#2D7080'], ['sessions', 'Sessions', '#E7B84B']], [['screenPageViews', 'Vues', '#0B2638']]]) {
            const target = new Element('div');
            context.chart(target, rows, series);
            const nodes = walk(target);
            assert.ok(nodes.some(n => n.tag === 'polyline'));
            assert.ok(!nodes.some(n => Object.values(n.attrs).some(v => /NaN|Infinity/.test(v))));
            const hits = nodes.filter(n => n.tag === 'rect' && n.attrs.tabindex === '0');
            assert.equal(hits.length, days + 1);
            hits.at(-1).handlers.mouseenter();
            const tooltip = nodes.find(n => n.className === 'ga-tooltip');
            assert.ok(tooltip.textContent.includes('26/10/2026'));
            assert.ok(tooltip.textContent.includes(series.length > 1 ? 'Sessions : 1' : 'Vues : 2'));
            hits[0].handlers.focus();
            assert.ok(tooltip.textContent.includes('Aucune ligne GA4'));
        }
    }
}
const empty = context.dailySeries([], 28, meta);
assert.equal(empty.length, 29);
assert.ok(empty.every(r => r.activeUsers === 0 && r.screenPageViews === 0));
const midnight = context.dailySeries([], 7, { timezone: 'Europe/Brussels', fetched_at: '2026-10-26T23:30:00Z' });
assert.equal(midnight.at(-1).date, '20261027', 'Use property timezone rather than browser/UTC date');
const leap = context.dailySeries([{ date: '20240229', activeUsers: 3, sessions: 4, screenPageViews: 8 }], 7, { timezone: 'UTC', fetched_at: '2024-03-02T12:00:00Z' });
assert.equal(leap.find(r => r.date === '20240229').screenPageViews, 8);
console.log('GA4 charts passed: all periods, zero filling, preserved API values, timezone/DST/leap dates, responsive lines and tooltips.');
