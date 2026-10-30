(() => {
    const definition = document.getElementById('survey-questions');
    const form = document.querySelector('.survey-form');
    if (!definition || !form) return;
    const questions = JSON.parse(definition.textContent);
    function update() {
        const visible = new Set();
        const values = Object.fromEntries(new FormData(form).entries());
        for (const question of questions) {
            const condition = question.condition;
            let show = true;
            if (condition) {
                const value = values[`answers[${condition.question_id}]`];
                const matches = condition.values.includes(value);
                show = visible.has(condition.question_id) && Boolean(value)
                    && (condition.operator === 'in' ? matches : !matches);
            }
            const field = form.querySelector(`[data-question="${question.id}"]`);
            field.hidden = !show;
            if (question.id !== 'q1') {
                field.querySelectorAll('input, select, textarea').forEach(input => { input.disabled = !show; });
            }
            if (show) {
                visible.add(question.id);
                const control = form.elements.namedItem(`answers[${question.id}]`);
                if (control) values[`answers[${question.id}]`] = control.value;
            }
        }
    }
    form.addEventListener('change', update);
    update();
    // Use the existing CSRF-protected POST to save an email choice before finalization.
    // The signed GET remains read-only for email link scanners and preview tools.
    if (form.dataset?.emailAnswer === 'pending') {
        const confirm = form.querySelector('button[name="intent"][value="confirm_q1"]');
        if (confirm) {
            confirm.formNoValidate = true;
            form.requestSubmit(confirm);
        }
    }
})();
