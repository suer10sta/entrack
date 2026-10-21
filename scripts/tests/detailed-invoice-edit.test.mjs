import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import {readFileSync} from 'node:fs';

const script = readFileSync(new URL('../../public/js/dalytrac-detailed-invoice-edit.js', import.meta.url), 'utf8');
function setup() {
    const events = {}, totals = {}, labels = new Map();
    let list = [];
    function row(index, quantity, price) {
        const inputs = ['description', 'quantity', 'unit_price', 'total'].map((field, i) => ({
            id: `detail-${index}-${field}`, name: i === 3 ? '' : `details[${index}][${field}]`,
            value: i === 1 ? quantity : i === 2 ? price : '', readOnly: i === 3,
            removeAttribute() {}, focus() {}
        }));
        const rowLabels = inputs.map(input => ({htmlFor: input.id}));
        const selectors = {'.quantity-input': inputs[1], '.price-input': inputs[2], '.total-input': inputs[3], '[name]': inputs[0], input: inputs[0]};
        const result = {
            querySelector: selector => selectors[selector],
            querySelectorAll: selector => selector === 'input' ? inputs : rowLabels,
            cloneNode: () => row(index, quantity, price),
            remove: () => { list = list.filter(item => item !== result); }
        };
        labels.set(result, rowLabels);
        return result;
    }
    list.push(row(0, '2', '700'));
    const container = {querySelectorAll: () => list, addEventListener: (name, callback) => { events[name] = callback; }, appendChild: item => list.push(item)};
    const remove = {addEventListener: (_, callback) => {events.remove = callback;}};
    const add = {addEventListener: (_, callback) => {events.add = callback;}};
    const form = {dataset: {locale: 'fr', taxRate: '21'}, querySelector(selector) {
        if (selector === '#detailsContainer') return container;
        if (selector === '#deleteRowBtn') return remove;
        if (selector === '#addRowBtn') return add;
        return totals[selector] ??= {};
    }};
    vm.runInNewContext(script, {document: {getElementById: () => form}, Intl});
    return {events, totals, remove, labels, rows: () => list};
}

test('initial totals reflect saved lines including VAT, without jQuery', () => {
    const ui = setup();
    assert.equal(ui.rows()[0].querySelector('.total-input').value, '1400.00');
    assert.equal(ui.totals['#grandTotal'].textContent, new Intl.NumberFormat('fr', {style: 'currency', currency: 'EUR'}).format(1400));
    assert.equal(ui.totals['#taxTotal'].textContent, new Intl.NumberFormat('fr', {style: 'currency', currency: 'EUR'}).format(294));
    assert.equal(ui.totals['#invoiceTotal'].textContent, new Intl.NumberFormat('fr', {style: 'currency', currency: 'EUR'}).format(1694));
    assert.equal(ui.remove.disabled, true);
});

test('adding, editing, deleting and adding again maintains totals and unique field/label IDs', () => {
    const ui = setup();
    ui.events.add();
    const second = ui.rows()[1];
    assert.equal(ui.remove.disabled, false);
    assert.equal(second.querySelector('[name]').name, 'details[1][description]');
    assert.equal(ui.labels.get(second)[0].htmlFor, second.querySelector('[name]').id);
    second.querySelector('.quantity-input').value = '1.5';
    second.querySelector('.price-input').value = '100';
    ui.events.input();
    assert.equal(second.querySelector('.total-input').value, '150.00');
    assert.equal(ui.totals['#grandTotal'].textContent, new Intl.NumberFormat('fr', {style: 'currency', currency: 'EUR'}).format(1550));
    ui.events.remove(); ui.events.remove();
    assert.equal(ui.rows().length, 1);
    ui.events.add();
    assert.equal(ui.rows()[1].querySelector('.total-input').value, '0.00');
    assert.equal(new Set(ui.rows().map(row => row.querySelector('[name]').id)).size, 2);
});
