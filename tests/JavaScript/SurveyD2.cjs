const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const questions = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', ['-r',
    "echo json_encode((require 'config/surveys.php')['types']['D2']['versions'][1]['questions'], JSON_THROW_ON_ERROR);",
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

test('D2 always offers optional assistance and requires Q1', () => { assert.deepEqual(form().visible(), ['q1', 'q4']); });
test('D2 usage and discovery show feature, result and assistance', () => {
    for (const q1 of ['usage_reel', 'decouverte']) assert.deepEqual(form({q1}).visible(), ['q1', 'q2', 'q3', 'q4']);
});
test('D2 blocker and project delay only show assistance', () => {
    for (const q1 of ['blocage', 'pas_temps', 'pas_donnees']) assert.deepEqual(form({q1}).visible(), ['q1', 'q4']);
});
test('D2 Other text is disabled when its branch is hidden and restored on return', () => {
    const page = form({q1: 'usage_reel', q2: 'Autre (texte libre)'});
    assert.deepEqual(page.visible(), ['q1', 'q2', 'q2_autre', 'q3', 'q4']);
    page.set('q1', 'blocage');
    assert.equal(page.controls.q2_autre.disabled, true);
    page.set('q1', 'usage_reel');
    assert.equal(page.controls.q2_autre.disabled, false);
    page.set('q2', 'Les devis');
    assert.equal(page.controls.q2_autre.disabled, true);
});
