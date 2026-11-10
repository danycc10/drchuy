import { initEditor } from './editor';
import { initNavigation } from './navigation';
import { initMedia } from './media';
import { initFaq } from './faq';
import { initConfirmations } from './confirmations';

initFaq(document);
initEditor(document);
initConfirmations(document);
initNavigation(document);
initMedia(document);
const editor = document.querySelector('[data-editor]');

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-closed-toggle]')) {
        event.target.closest('[data-hours-day]').querySelector('[data-hours-times]').hidden = event.target.checked;
    }
});
const exceptions = document.querySelector('[data-exceptions]');
let exceptionIndex = Math.max(-1, ...Array.from(exceptions?.querySelectorAll('input[type=date]') ?? [])
    .map((input) => Number(input.name.match(/\[exceptions\]\[(\d+)\]/)?.[1] ?? -1))) + 1;
document.querySelector('[data-add-exception]')?.addEventListener('click', () => {
    if (exceptions.querySelectorAll('[data-exception]').length >= 20) {
        window.alert('Puedes agregar hasta 20 fechas especiales.');
        return;
    }
    const template = document.querySelector('#exception-template').innerHTML.replaceAll('__INDEX__', String(exceptionIndex++));
    exceptions.insertAdjacentHTML('beforeend', template);
    exceptions.lastElementChild.querySelector('input[type=date]').focus();
    editor.dispatchEvent(new Event('input', { bubbles: true }));
});
exceptions?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-remove-exception]');
    if (button) {
        button.closest('[data-exception]').remove();
        editor.dispatchEvent(new Event('input', { bubbles: true }));
    }
});
