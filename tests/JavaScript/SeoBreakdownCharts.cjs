// Run: node tests/JavaScript/SeoBreakdownCharts.cjs
const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict'), path = require('node:path');
class Element {
    constructor(tag) { this.tag = tag; this.children = []; this.attrs = {}; this.style = {}; this.handlers = {}; this.clientWidth = 500; this.classList = { add() {} }; }
    append(...nodes) { this.children.push(...nodes); }
    replaceChildren(...nodes) { this.children = nodes; }
    setAttribute(key, value) { this.attrs[key] = String(value); }
    addEventListener(key, fn) { this.handlers[key] = fn; }
}
let disconnected = 0;
const context = { window: {}, Intl, document: { createElement: tag => new Element(tag), createElementNS: (_, tag) => new Element(tag) }, ResizeObserver: class { observe() {} disconnect() { disconnected++; } } };
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(__dirname, '../../public/js/seo-breakdown-chart.js'), 'utf8'), context);
const chart = context.window.DalyTracBreakdown;
const walk = node => [node, ...node.children.flatMap(walk)];
assert.equal(chart.label('esp', true), 'Espagne'); assert.equal(chart.label('QAT', true), 'Qatar');
assert.equal(chart.label('MOBILE', false), 'Mobile');
const metrics = [{ key: 'impressions', label: 'Impressions' }, { key: 'clicks', label: 'Clics' }, { key: 'ctr', label: 'CTR', percent: true, digits: 2 }];
for (const count of [0, 1, 3, 250]) {
    const target = new Element('div');
    const rows = Array.from({ length: count }, (_, i) => ({ label: `Pays ${i}`, impressions: i + 1, clicks: i % 3, ctr: .125 }));
    const original = JSON.stringify(rows);
    chart.render(target, rows, metrics, 'impressions');
    assert.equal(JSON.stringify(rows), original, 'Never mutate Google measurements');
    if (!count) { assert.match(target.textContent, /Aucune donnée/); continue; }
    let nodes = walk(target);
    const hits = nodes.filter(n => n.tag === 'rect' && n.attrs.tabindex === '0');
    assert.equal(hits.length, count);
    hits[0].handlers.mouseenter();
    const tooltip = nodes.find(n => n.className === 'seo-breakdown-tooltip');
    assert.match(tooltip.textContent, new RegExp(`Impressions : ${count}`));
    assert.match(tooltip.textContent, /CTR : 12,5 %/);
    hits[0].handlers.blur(); assert.equal(tooltip.hidden, true);
    const select = nodes.find(n => n.tag === 'select'); select.value = 'clicks'; select.handlers.change();
    nodes = walk(target);
    assert.ok(!nodes.some(n => Object.values(n.attrs).some(v => /NaN|Infinity/.test(v))));
    chart.render(target, [{ label: 'Mobile', impressions: 0, clicks: 0, ctr: 0 }], metrics, 'impressions');
    assert.equal(walk(target).filter(n => n.tag === 'rect' && n.attrs.tabindex === '0').length, 1);
    const bar = walk(target).find(n => n.tag === 'rect' && n.attrs.rx === '3');
    assert.equal(bar.attrs.width, '0', 'Zero values must never become artificial positive bars');
    chart.destroy(target);
}
assert.ok(disconnected >= 6, 'Period changes clean up chart observers');
const ga = [{ label: 'Desktop', activeUsers: 4, sessions: 7, screenPageViews: 12 }];
const target = new Element('div');
chart.render(target, ga, [{ key: 'activeUsers', label: 'Utilisateurs actifs' }, { key: 'sessions', label: 'Sessions' }, { key: 'screenPageViews', label: 'Pages vues' }], 'activeUsers');
walk(target).find(n => n.attrs.tabindex === '0').handlers.focus();
assert.match(walk(target).find(n => n.className === 'seo-breakdown-tooltip').textContent, /Sessions : 7/);
console.log('Breakdown charts passed: GSC/GA4 data, country labels, devices, metric changes, tooltips, empty/zero data, many categories and period refresh.');
