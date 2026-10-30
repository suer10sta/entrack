const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const questions = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', ['-r',
    "echo json_encode((require 'config/surveys.php')['types']['D4']['versions'][1]['questions'], JSON_THROW_ON_ERROR);",
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

test('D4 shows the reason, usage, support and call questions', () => { assert.deepEqual(form().visible(), ['q1','q2','q3','q4']); });
test('D4 Other text is shown only for the canonical Other reason', () => { assert.deepEqual(form({q1:'Autre (texte libre)'}).visible(), ['q1','q1_autre','q2','q3','q4']); });
test('D4 hidden Other text is disabled and can be restored', () => { const page=form({q1:'Autre (texte libre)',q1_autre:'Besoin particulier'}); page.set('q1','La prise en main était trop difficile'); assert.equal(page.controls.q1_autre.disabled,true);page.set('q1','Autre (texte libre)');assert.equal(page.controls.q1_autre.disabled,false); });
