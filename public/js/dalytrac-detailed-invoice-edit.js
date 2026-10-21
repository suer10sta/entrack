(() => {
    const form = document.getElementById('detailedInvoiceForm');
    if (!form) return;
    const container = form.querySelector('#detailsContainer');
    const remove = form.querySelector('#deleteRowBtn');
    const money = new Intl.NumberFormat(form.dataset.locale, {style: 'currency', currency: 'EUR'});
    const taxRate = Number(form.dataset.taxRate) || 0;
    const rows = () => Array.from(container.querySelectorAll('.detail-row'));
    function recalculate() {
        let subtotal = 0;
        rows().forEach(row => {
            const total = (Number(row.querySelector('.quantity-input').value) || 0)
                * (Number(row.querySelector('.price-input').value) || 0);
            row.querySelector('.total-input').value = total.toFixed(2);
            subtotal += total;
        });
        form.querySelector('#grandTotal').textContent = money.format(subtotal);
        form.querySelector('#taxTotal').textContent = money.format(subtotal * taxRate / 100);
        form.querySelector('#invoiceTotal').textContent = money.format(subtotal * (1 + taxRate / 100));
        remove.disabled = rows().length <= 1;
    }
    container.addEventListener('input', recalculate);
    form.querySelector('#addRowBtn').addEventListener('click', () => {
        const currentRows = rows();
        const clone = currentRows[currentRows.length - 1].cloneNode(true);
        // Use the highest existing index, so deleting/adding never duplicates names.
        const nextIndex = Math.max(...currentRows.map(row => Number(row.querySelector('[name]').name.match(/\[(\d+)\]/)[1]))) + 1;
        clone.querySelectorAll('input').forEach(input => {
            input.value = input.readOnly ? '0.00' : '';
            input.removeAttribute('aria-invalid');
            input.id = input.id.replace(/detail-\d+-/, `detail-${nextIndex}-`);
            if (input.name) input.name = input.name.replace(/\[\d+\]/, `[${nextIndex}]`);
        });
        clone.querySelectorAll('label').forEach(label => {
            label.htmlFor = label.htmlFor.replace(/detail-\d+-/, `detail-${nextIndex}-`);
        });
        container.appendChild(clone);
        clone.querySelector('input').focus();
        recalculate();
    });
    remove.addEventListener('click', () => {
        const currentRows = rows();
        if (currentRows.length > 1) currentRows[currentRows.length - 1].remove();
        recalculate();
    });
    recalculate();
})();
