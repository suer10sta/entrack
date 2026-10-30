const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const questions = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', ['-r',
    "echo json_encode((require 'config/surveys.php')['types']['D3']['versions'][1]['questions'], JSON_THROW_ON_ERROR);",
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

test('D3 offers Q1 through Q4 and hides conditional controls initially', () => { assert.deepEqual(form().visible(), ['q1','q2','q3','q4']); });
test('D3 offers a plan only for both strong intention choices', () => { for(const q4 of ['Très probable','Plutôt probable']) assert.deepEqual(form({q1:'4',q4}).visible(), ['q1','q2','q3','q4','q5']); });
test('D3 ignores a hidden plan when intention changes', () => { const page=form({q1:'4',q4:'Très probable',q5:'Solo'}); page.set('q4','Incertain'); assert.equal(page.controls.q5.disabled,true); page.set('q4','Plutôt probable'); assert.equal(page.controls.q5.disabled,false); });
test('D3 other text follows the most useful feature choice', () => { const page=form({q1:'4'}); page.set('q2','Autre (texte libre)'); assert.equal(page.controls.q2_autre.disabled,false); page.set('q2','Devis'); assert.equal(page.controls.q2_autre.disabled,true); });
