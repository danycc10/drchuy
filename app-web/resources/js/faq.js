import { confirmAction } from './confirmations.js';

export function initFaq(document) {
    const root = document.querySelector('[data-faq-editor]');
    if (!root) return;
    const window = document.defaultView;
    const editor = root.closest('form');
    const list = root.querySelector('[data-faq-items]');
    const add = root.querySelector('[data-faq-add]');
    const status = root.querySelector('[data-faq-status]');
    let nextId = list.children.length;
    const items = () => [...list.querySelectorAll('[data-faq-item]')];

    function refresh(message = '') {
        const rows = items();
        rows.forEach((row, index) => {
            row.querySelector('[data-faq-number]').textContent = `Pregunta ${index + 1}`;
            row.querySelector('[data-faq-actions]').hidden = false;
            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/\[items\]\[[^\]]+\]/, `[items][${index}]`);
            });
            for (const action of ['up', 'down', 'remove']) {
                const button = row.querySelector(`[data-faq-${action}]`);
                button.setAttribute('aria-label', `${{ up: 'Subir', down: 'Bajar', remove: 'Quitar' }[action]} pregunta ${index + 1}`);
                button.disabled = action === 'up' ? index === 0 : action === 'down' ? index === rows.length - 1 : false;
            }
        });
        add.hidden = false;
        add.disabled = rows.length >= 12;
        root.querySelector('[data-faq-empty]').hidden = rows.length !== 0;
        status.textContent = `${message ? `${message} ` : ''}${rows.length} de 12 preguntas.`;
    }

    add.addEventListener('click', () => {
        if (items().length >= 12) return;
        const html = root.querySelector('[data-faq-template]').innerHTML.replaceAll('__INDEX__', String(nextId++));
        list.insertAdjacentHTML('beforeend', html);
        refresh('Pregunta agregada.');
        list.lastElementChild.querySelector('input:not([type])').focus();
        editor.dispatchEvent(new window.Event('input', { bubbles: true }));
    });

    list.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-faq-up], [data-faq-down], [data-faq-remove]');
        if (!button || button.disabled) return;
        const row = button.closest('[data-faq-item]');
        if (button.hasAttribute('data-faq-remove')) {
            const confirmed = await confirmAction(document, {
                title: 'Quitar pregunta', label: 'Quitar del borrador',
                message: 'El cambio se aplicará al guardar el borrador. La versión pública se conserva hasta publicar.',
                detail: row.querySelector('input:not([type])')?.value || row.querySelector('[data-faq-number]').textContent,
            }, button);
            if (!confirmed || !row.isConnected) return;
            const neighbor = row.nextElementSibling ?? row.previousElementSibling;
            row.remove();
            refresh('Pregunta quitada.');
            (neighbor?.querySelector('input:not([type])') ?? add).focus();
        } else {
            if (button.hasAttribute('data-faq-up')) list.insertBefore(row, row.previousElementSibling);
            else list.insertBefore(row.nextElementSibling, row);
            refresh('Orden actualizado.');
            (button.disabled ? row.querySelector('input:not([type])') : button).focus();
        }
        editor.dispatchEvent(new window.Event('input', { bubbles: true }));
    });
    refresh();
}
