const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const questions = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', ['-r',
    "echo json_encode((require 'config/surveys.php')['types']['D6']['versions'][1]['questions'], JSON_THROW_ON_ERROR);",
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

test('D6 initially shows reason and return intention',()=>{assert.deepEqual(form().visible(),['q1','q2']);});
test('D6 alternative plan is offered only for price or unmet needs',()=>{for(const q1 of ['Le prix de mon forfait ne me convient plus','Dalytrac ne répond pas suffisamment à mes besoins'])assert.deepEqual(form({q1}).visible(),['q1','q2','q3']);});
test('D6 recall question follows the exact non-No catalogue condition',()=>{const page=form({q1:'Je prévois de reprendre prochainement',q2:'Probablement'});assert.equal(page.controls.q4.disabled,false);page.set('q2','Non');assert.equal(page.controls.q4.disabled,true);page.set('q2','Je ne sais pas');assert.equal(page.controls.q4.disabled,false);});
test('D6 hides and restores Other text and hidden plan controls',()=>{const page=form({q1:'Autre (texte libre)'});assert.equal(page.controls.q1_autre.disabled,false);page.set('q1','Le prix de mon forfait ne me convient plus');assert.equal(page.controls.q1_autre.disabled,true);assert.equal(page.controls.q3.disabled,false);page.set('q1','Je rencontre un problème technique');assert.equal(page.controls.q3.disabled,true);});
