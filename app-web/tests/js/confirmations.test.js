import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { JSDOM } from 'jsdom';
import { confirmAction, initConfirmations } from '../../resources/js/confirmations.js';
import { initEditor } from '../../resources/js/editor.js';

const markup = readFileSync(new URL('../../resources/views/components/confirmation-dialog.blade.php', import.meta.url), 'utf8');
function setup(extra = '') {
    const { window } = new JSDOM(`${extra}<form data-confirm="Confirmar la versión" data-confirm-detail="Inicio · Versión #12">
        <input name="response" value="Revisado" required>
        <button name="decision" value="changes" data-confirm="Devolver con comentarios">Solicitar cambios</button>
        <button name="decision" value="publish" data-confirm="Actualizar el sitio">Aprobar y publicar</button>
        </form>${markup}`, { url: 'https://clinic.test/admin/reviews/1' });
    const document = window.document;
    const find = (selector) => document.querySelector(selector);
    const dialog = find('dialog');
    // jsdom does not implement modal focus containment or the top layer.
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.dispatchEvent(new window.Event('close')); };
    initEditor(document);
    initConfirmations(document);
    const submissions = [];
    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        submissions.push(new window.FormData(event.target, event.submitter));
        event.preventDefault(); // Observe submission without navigating in jsdom.
    });
    return { window, document, find, dialog, submissions };
}
const flush = () => new Promise((resolve) => setImmediate(resolve));

test('cancel and Escape restore focus, keep fields and display untrusted context as text', async () => {
    const { document, window, find, dialog, submissions } = setup();
    const button = find('[value=publish]');
    for (const useEscape of [false, true]) {
        button.focus();
        button.click();
        assert.equal(dialog.open, true);
        assert.equal(document.activeElement, find('[data-confirm-cancel]'));
        assert.equal(find('[data-confirm-title]').textContent, 'Aprobar y publicar');
        assert.equal(find('[data-confirm-message]').textContent, 'Actualizar el sitio');
        if (useEscape) dialog.dispatchEvent(new window.Event('cancel', { cancelable: true }));
        else find('[data-confirm-cancel]').click();
        await flush();
        assert.equal(dialog.open, false);
        assert.equal(document.activeElement, button);
        assert.equal(find('[name=response]').value, 'Revisado');
        assert.equal(submissions.length, 0);
    }
    const promise = confirmAction(document, { message: 'Prueba', detail: '<img src=x onerror=alert(1)>' }, button);
    assert.equal(dialog.querySelector('[data-confirm-detail]').textContent, '<img src=x onerror=alert(1)>');
    assert.equal(dialog.querySelector('img'), null);
    find('[data-confirm-cancel]').click();
    assert.equal(await promise, false);
});

test('confirmation preserves either decision and prevents double sends until browser Back', async () => {
    for (const decision of ['changes', 'publish']) {
        const { window, find, dialog, submissions } = setup();
        const button = find(`[value=${decision}]`);
        button.click();
        button.click();
        find('[data-confirm-accept]').click();
        find('[data-confirm-accept]').click();
        await flush();
        assert.equal(submissions.length, 1);
        assert.equal(submissions[0].get('decision'), decision);
        assert.equal(submissions[0].get('response'), 'Revisado');
        assert.equal(find('form').getAttribute('aria-busy'), 'true');
        button.click();
        assert.equal(dialog.open, false);
        assert.equal(submissions.length, 1);
        window.dispatchEvent(new window.Event('pageshow'));
        assert.equal(find('form').hasAttribute('aria-busy'), false);
        button.click();
        assert.equal(dialog.open, true);
        find('[data-confirm-cancel]').click();
        await flush();
    }
});

test('native validation runs before opening and again after confirming', async () => {
    const { find, dialog, submissions } = setup();
    const field = find('[name=response]');
    field.value = '';
    find('[value=publish]').click();
    assert.equal(dialog.open, false);
    field.value = 'Revisado';
    find('[value=publish]').click();
    assert.equal(dialog.open, true);
    field.value = '';
    find('[data-confirm-accept]').click();
    await flush();
    assert.equal(submissions.length, 0);
    field.value = 'Corregido';
    find('[value=changes]').click();
    find('[data-confirm-accept]').click();
    await flush();
    assert.equal(submissions.length, 1);
    assert.equal(submissions[0].get('decision'), 'changes');
});

test('unsaved editor fields block confirmation and return focus to saving', async () => {
    const { window, document, find, dialog, submissions } = setup('<button data-save-button>Guardar</button><form data-editor><input name="title" value="Antes"></form>');
    find('[name=title]').value = 'Después';
    find('[name=title]').dispatchEvent(new window.Event('input', { bubbles: true }));
    find('[value=publish]').click();
    await flush();
    assert.equal(dialog.open, false);
    assert.equal(submissions.length, 0);
    assert.equal(document.activeElement, find('[data-save-button]'));
});

test('leaving the page cancels a pending confirmation without submitting on return', async () => {
    const { window, find, dialog, submissions } = setup();
    find('[value=publish]').click();
    window.dispatchEvent(new window.Event('pagehide'));
    window.dispatchEvent(new window.Event('pageshow'));
    await flush();
    assert.equal(dialog.open, false);
    assert.equal(submissions.length, 0);
    find('[value=publish]').click();
    assert.equal(dialog.open, true);
    find('[data-confirm-cancel]').click();
    await flush();
});
