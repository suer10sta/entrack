const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const questions = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', ['-r',
    "echo json_encode((require 'config/surveys.php')['types']['D1']['versions'][1]['questions'], JSON_THROW_ON_ERROR);",
], { cwd: root, encoding: 'utf8' }));
const source = fs.readFileSync(path.join(root, 'public/js/survey-d1.js'), 'utf8');

function form(initial = {}) {
    const controls = Object.fromEntries(questions.map(q => [q.id, { value: initial[q.id] || '', disabled: false }]));
    const fields = Object.fromEntries(questions.map(q => [q.id, {
        hidden: false,
        querySelectorAll: () => [controls[q.id]],
    }]));
    let changed;
    const dom = {
        elements: { namedItem: name => controls[name.match(/^answers\[(.+)\]$/)[1]] },
        querySelector: selector => fields[selector.match(/data-question="(.+)"/)[1]],
        addEventListener: (_, callback) => { changed = callback; },
    };
    vm.runInNewContext(source, {
        document: {
            getElementById: () => ({ textContent: JSON.stringify(questions) }),
            querySelector: () => dom,
        },
        FormData: class {
            entries() {
                return Object.entries(controls).filter(([, c]) => !c.disabled)
                    .map(([id, c]) => [`answers[${id}]`, c.value])[Symbol.iterator]();
            }
        },
    });
    return {
        controls,
        visible: () => Object.entries(fields).filter(([, field]) => !field.hidden).map(([id]) => id),
        set: (id, value) => { controls[id].value = value; changed(); },
    };
}

test('no answer shows only the required first question', () => {
    assert.deepEqual(form().visible(), ['q1']);
});
test('success hides all optional questions and disables their controls', () => {
    const page = form({ q1: 'reussi', q2: 'Un message d’erreur s’affiche' });
    assert.deepEqual(page.visible(), ['q1']);
    assert.equal(page.controls.q2b.disabled, true);
});
test('abandon shows only the reason question', () => {
    assert.deepEqual(form({ q1: 'abandon' }).visible(), ['q1', 'q4']);
});
test('technical difficulty shows error details only for the relevant choice', () => {
    const page = form({ q1: 'connexion' });
    assert.deepEqual(page.visible(), ['q1', 'q2', 'q3']);
    page.set('q2', 'Un message d’erreur s’affiche');
    assert.deepEqual(page.visible(), ['q1', 'q2', 'q2b', 'q3']);
});
test('switching back to an error answer restores its text field immediately', () => {
    const page = form({ q1: 'connexion', q2: 'Un message d’erreur s’affiche' });
    page.set('q1', 'reussi');
    assert.deepEqual(page.visible(), ['q1']);
    page.set('q1', 'connexion');
    assert.deepEqual(page.visible(), ['q1', 'q2', 'q2b', 'q3']);
    assert.equal(page.controls.q2b.disabled, false);
});
test('the Other free-text field follows its parent choice', () => {
    const page = form({ q1: 'configuration' });
    page.set('q2', 'Autre (texte libre)');
    assert.deepEqual(page.visible(), ['q1', 'q2', 'q2_autre', 'q3']);
    page.set('q1', 'abandon');
    assert.equal(page.controls.q2_autre.disabled, true);
});

function emailLanding(state, hasConfirmButton = true) {
    const submissions = [];
    const confirm = { name: 'intent', value: 'confirm_q1', formNoValidate: false };
    const dom = {
        dataset: { emailAnswer: state },
        addEventListener: () => {},
        querySelector: selector => {
            assert.equal(selector, 'button[name="intent"][value="confirm_q1"]');
            return hasConfirmButton ? confirm : null;
        },
        requestSubmit: button => submissions.push(button),
    };
    vm.runInNewContext(source, {
        document: {
            getElementById: () => ({ textContent: '[]' }),
            querySelector: () => dom,
        },
        FormData: class { entries() { return [][Symbol.iterator](); } },
    });
    return { submissions, confirm };
}

test('an email choice automatically submits Q1 confirmation without final form validation', () => {
    const page = emailLanding('pending');
    assert.equal(page.submissions.length, 1);
    assert.equal(page.submissions[0], page.confirm);
    assert.equal(page.confirm.value, 'confirm_q1');
    assert.equal(page.confirm.formNoValidate, true);
});

test('saved answers and pages without an email choice never auto-submit', () => {
    assert.equal(emailLanding('none').submissions.length, 0);
    assert.equal(emailLanding(undefined).submissions.length, 0);
});

test('the final submission button is never used to automatically save an email choice', () => {
    assert.equal(emailLanding('pending', false).submissions.length, 0);
});
