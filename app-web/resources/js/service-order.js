export function initServiceOrder(document) {
    const form = document.querySelector('[data-service-order]');
    if (!form) return;
    const list = form.querySelector('[data-order-list]');
    const status = form.querySelector('[data-order-status]');
    let dragged = null;
    const refresh = () => {
        [...list.children].forEach((row, index) => {
            row.querySelector('.order-number').textContent = index + 1;
            row.querySelector('[data-order-up]').disabled = index === 0;
            row.querySelector('[data-order-down]').disabled = index === list.children.length - 1;
        });
    };
    const changed = (row) => {
        refresh();
        status.textContent = `${row.querySelector('[data-order-name]').textContent}: posición ${[...list.children].indexOf(row) + 1}. Guarda el nuevo orden.`;
        form.dispatchEvent(new document.defaultView.Event('input', { bubbles: true }));
    };
    [...list.children].forEach((row) => {
        row.draggable = true;
        row.querySelector('[data-order-controls]').hidden = false;
        row.addEventListener('dragstart', (event) => {
            dragged = row;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', row.querySelector('[name="order[]"]').value);
            row.classList.add('is-dragging');
        });
        row.addEventListener('dragend', () => { row.classList.remove('is-dragging'); dragged = null; });
        row.addEventListener('dragover', (event) => { if (dragged && dragged !== row) event.preventDefault(); });
        row.addEventListener('drop', (event) => {
            if (!dragged || dragged === row) return;
            event.preventDefault();
            const rows = [...list.children];
            list.insertBefore(dragged, rows.indexOf(dragged) < rows.indexOf(row) ? row.nextElementSibling : row);
            changed(dragged);
        });
    });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('[data-order-up], [data-order-down]');
        if (!button || button.disabled) return;
        const row = button.closest('[data-order-item]');
        if (button.hasAttribute('data-order-up')) list.insertBefore(row, row.previousElementSibling);
        else list.insertBefore(row.nextElementSibling, row);
        changed(row);
        (button.disabled ? row.querySelector(button.hasAttribute('data-order-up') ? '[data-order-down]' : '[data-order-up]') : button).focus();
    });
    refresh();
}
