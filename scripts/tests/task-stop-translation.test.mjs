import {readFileSync} from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const blade = readFileSync(new URL('../../resources/views/components/chrono-stop-modal.blade.php', import.meta.url), 'utf8');
const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/@(?:json|js)\(__\('[^']*'\)\)/g, '"label"');
const flush = () => new Promise(resolve => setImmediate(resolve));
function setup(stopResponse = {ok: true}) {
    const elements = new Map(), calls = [], pending = [], documentListeners = {};
    function element(id) {
        if (!elements.has(id)) {
            const listeners = {}, attributes = {};
            const el = {id, value: '', textContent: '', hidden: true, disabled: false, style: {}, classList: {add() {}, remove() {}},
                setAttribute(name, value) {attributes[name] = value;}, getAttribute(name) {return attributes[name];},
                focus() {document.activeElement = el;},
                contains(target) {return target?.id === 'chronoStopTranslateBtn' || target?.id?.startsWith('chronoStopLanguage');},
                addEventListener(event, callback) {(listeners[event] ??= []).push(callback);},
                fire(event, data = {}) {for (const callback of listeners[event] ?? []) callback({target: el, preventDefault() {}, ...data});},
                click() {if (!el.disabled) el.fire('click');}};
            elements.set(id, el);
        }
        return elements.get(id);
    }
    const document = {getElementById: element, activeElement: null,
        querySelector: () => ({getAttribute: () => 'csrf'}),
        addEventListener(event, callback) {(documentListeners[event] ??= []).push(callback);}};
    const window = {dispatchEvent() {}};
    vm.runInNewContext(script, {window, document, FormData, CustomEvent: class {},
        fetch(url, options) {
            calls.push({url, options});
            return url === '/tasks/prepare-stop-comment' ? new Promise(resolve => pending.push(resolve)) : Promise.resolve(stopResponse);
        }});
    const open = handler => window.ChronoStopModal.open('/tasks/7/stop', handler);
    open();
    const text = element('chronoStopText'); text.value = 'Worked on plans.'; text.fire('input');
    return {text, calls, element, document, open, window,
        respond(response) {pending.shift()(response);},
        documentEvent(event, data) {for (const callback of documentListeners[event] ?? []) callback(data);}};
}
const success = text => ({ok: true, json: async () => ({corrected: text})});

test('stopping corrects spelling in the input language without requesting translation', async () => {
    const ui = setup();
    ui.text.value = '  Worked on plans.\nUncorrected text!  '; ui.text.fire('input');
    ui.element('chronoStopConfirmBtn').click(); await flush();
    assert.equal(ui.calls.length, 1);
    assert.equal(ui.calls[0].url, '/tasks/prepare-stop-comment');
    assert.equal(JSON.parse(ui.calls[0].options.body).translate, false);
    ui.respond(success('Worked on plans. Corrected text!')); await flush();
    assert.equal(ui.calls[1].url, '/tasks/7/stop');
    assert.equal(ui.calls[1].options.body.get('stop_comment'), 'Worked on plans. Corrected text!');
    assert.equal(ui.element('chronoStopModal').style.display, 'none');
});

test('Translate only opens the upward menu and each language triggers the selected translation', async () => {
    for (const language of ['fr', 'en', 'nl', 'es']) {
        const ui = setup();
        const menu = ui.element('chronoStopLanguageMenu');
        assert.equal(menu.hidden, true);
        ui.element('chronoStopTranslateBtn').click();
        assert.equal(menu.hidden, false);
        assert.equal(ui.calls.length, 0);
        ui.element('chronoStopLanguage-' + language).click();
        assert.equal(menu.hidden, true);
        assert.deepEqual(JSON.parse(ui.calls[0].options.body), {comment: 'Worked on plans.', translate: true, language});
        assert.equal(ui.element('chronoStopConfirmBtn').disabled, true);
        ui.element('chronoStopLanguage-' + language).click();
        assert.equal(ui.calls.length, 1);
        ui.respond(success('Translated text')); await flush();
        assert.equal(ui.text.value, 'Translated text');
        assert.equal(ui.element('chronoStopModal').style.display, 'block');
        ui.text.value = 'Edited translated text'; ui.text.fire('input');
        ui.element('chronoStopConfirmBtn').click(); await flush();
        assert.equal(ui.calls.length, 2);
        assert.equal(JSON.parse(ui.calls[1].options.body).translate, false);
        ui.respond(success('Corrected translated text')); await flush();
        assert.equal(ui.calls[2].options.body.get('stop_comment'), 'Corrected translated text');
    }
});

test('opening and dismissing the menu never translates', async () => {
    const ui = setup();
    const button = ui.element('chronoStopTranslateBtn'), menu = ui.element('chronoStopLanguageMenu');
    button.click(); button.click();
    assert.equal(menu.hidden, true);
    button.click(); ui.documentEvent('click', {target: ui.text});
    assert.equal(menu.hidden, true);
    button.click(); ui.documentEvent('keydown', {key: 'Escape'});
    assert.equal(menu.hidden, true);
    assert.equal(ui.element('chronoStopModal').style.display, 'block');
    ui.element('chronoStopConfirmBtn').click(); await flush();
    assert.equal(ui.calls.length, 1);
    assert.equal(JSON.parse(ui.calls[0].options.body).translate, false);
    ui.respond(success('Worked on plans.')); await flush();
    assert.equal(ui.calls[1].options.body.get('stop_comment'), 'Worked on plans.');
});

test('language menu supports keyboard navigation', () => {
    const ui = setup();
    ui.element('chronoStopTranslateBtn').click();
    assert.equal(ui.document.activeElement.id, 'chronoStopLanguage-fr');
    ui.element('chronoStopLanguageMenu').fire('keydown', {key: 'ArrowDown'});
    assert.equal(ui.document.activeElement.id, 'chronoStopLanguage-en');
    ui.element('chronoStopLanguageMenu').fire('keydown', {key: 'ArrowUp'});
    assert.equal(ui.document.activeElement.id, 'chronoStopLanguage-fr');
});

test('failed translation preserves input and permits stopping without translation', async () => {
    const ui = setup();
    ui.element('chronoStopTranslateBtn').click(); ui.element('chronoStopLanguage-es').click();
    ui.respond({ok: false}); await flush();
    assert.equal(ui.text.value, 'Worked on plans.');
    assert.equal(ui.element('chronoStopConfirmBtn').disabled, false);
    ui.element('chronoStopConfirmBtn').click(); await flush();
    ui.respond(success('Worked on plans.')); await flush();
    assert.equal(ui.calls[2].options.body.get('stop_comment'), 'Worked on plans.');
});

test('cancel, blank input and reopening have no translation side effects', () => {
    const ui = setup();
    ui.text.value = ' '; ui.text.fire('input');
    assert.equal(ui.element('chronoStopTranslateBtn').disabled, true);
    ui.element('chronoStopTranslateBtn').click();
    ui.element('chronoStopConfirmBtn').click();
    ui.element('chronoStopCancelBtn').click();
    ui.open();
    assert.equal(ui.text.value, '');
    assert.equal(ui.element('chronoStopLanguageMenu').hidden, true);
    assert.equal(ui.calls.length, 0);
});

test('legacy submit handler receives the corrected description', async () => {
    const ui = setup(); let comment;
    ui.open(async value => {comment = value;});
    ui.text.value = '  Original description  '; ui.text.fire('input');
    ui.element('chronoStopConfirmBtn').click(); await flush();
    assert.equal(comment, undefined);
    ui.respond(success('Corrected description')); await flush();
    assert.equal(comment, 'Corrected description');
    assert.equal(ui.calls.length, 1);
});

test('failed stop preserves text and re-enables controls', async () => {
    const ui = setup({ok: false});
    ui.element('chronoStopConfirmBtn').click(); await flush();
    ui.respond(success('Worked on plans.')); await flush();
    assert.equal(ui.text.value, 'Worked on plans.');
    assert.equal(ui.element('chronoStopModal').style.display, 'block');
    assert.equal(ui.element('chronoStopConfirmBtn').disabled, false);
});

for (const response of [{ok: false}, success(''), success('x'.repeat(501))]) {
    test('failed or invalid correction preserves the text and does not stop the task: ' + JSON.stringify(response), async () => {
        const ui = setup();
        ui.element('chronoStopConfirmBtn').click();
        ui.element('chronoStopConfirmBtn').click();
        assert.equal(ui.calls.length, 1);
        ui.respond(response); await flush();
        assert.equal(ui.calls.length, 1);
        assert.equal(ui.text.value, 'Worked on plans.');
        assert.equal(ui.element('chronoStopModal').style.display, 'block');
        assert.equal(ui.element('chronoStopConfirmBtn').disabled, false);
    });
}
