// Run: node tests/JavaScript/ProspectReferralLink.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/prospect-referral-link.js'), 'utf8');

async function scenario(clipboard) {
    let ready, click, focused = false, selected = false;
    const input = {
        value: 'https://dalytrac.com/?utm_source=linkedin&utm_medium=message_prive&ref=' + 'a'.repeat(48),
        focus() { focused = true; }, select() { selected = true; },
    };
    const status = { textContent: '' };
    const button = { addEventListener(event, callback) { assert.equal(event, 'click'); click = callback; } };
    const section = { querySelector(selector) {
        return { '[data-prospect-link-value]': input, '[data-copy-prospect-link]': button, '[data-copy-prospect-link-status]': status }[selector];
    } };
    const context = {
        navigator: { clipboard },
        document: {
            addEventListener(event, callback) { assert.equal(event, 'DOMContentLoaded'); ready = callback; },
            querySelectorAll() { return [section]; },
        },
    };
    vm.runInNewContext(source, context);
    ready();
    await click();
    return { input, status, focused, selected };
}

(async () => {
    let copied;
    const success = await scenario({ async writeText(value) { copied = value; } });
    assert.equal(copied, success.input.value);
    assert.equal(success.status.textContent, 'Lien copié.');
    assert.equal(success.selected, false);
    for (const clipboard of [undefined, { async writeText() { throw new Error('Permission denied'); } }]) {
        const fallback = await scenario(clipboard);
        assert.equal(fallback.focused, true);
        assert.equal(fallback.selected, true);
        assert.match(fallback.status.textContent, /Ctrl\+C/);
    }
    console.log('Copy success and both manual-copy fallbacks passed.');
})().catch((error) => { console.error(error); process.exitCode = 1; });
