import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initEditor } from '../../resources/js/editor.js';

test('security errors reveal and focus the submitted form when fields share names', () => {
    const { window } = new JSDOM(`<div data-error-summary tabindex="-1"><span data-error-field="code">Código inválido</span></div>
        <details><form><input name="code" id="first-code"></form></details>
        <details id="submitted"><form data-error-target="true"><input name="code" id="submitted-code"></form></details>`);
    initEditor(window.document);
    window.document.querySelector('[data-error-field] a').click();
    assert.equal(window.document.getElementById('submitted').open, true);
    assert.equal(window.document.activeElement.id, 'submitted-code');
});

function setup({ recovered = false, published = false, pending = false, errors = '' } = {}) {
    const dom = new JSDOM(`<!doctype html><body>${errors}
        <p data-save-state role="status"></p><button data-save-button form="editor"><svg aria-hidden="true"></svg><span data-save-label>Guardar y publicar</span></button>
        <form id="editor" data-editor data-recovered-input="${recovered}" data-pending-publish="${pending}">
            <div data-language-tabs hidden><button type="button" id="tab-es" role="tab" aria-controls="language-es">Español</button><button type="button" id="tab-en" role="tab" aria-controls="language-en">Inglés</button></div>
            <section id="language-es" data-language-panel><label>Nombre<input id="name-es" name="payload[es][name]" value="Consulta" required></label></section>
            <section id="language-en" data-language-panel><label>Description<textarea id="description-en" name="payload[en][description]" maxlength="100" required>Consultation</textarea></label></section>
            <input type="hidden" name="visible" value="0"><input type="checkbox" name="visible" value="1" checked>
        </form><p data-dirty-note hidden></p><form data-confirm="Publicar"><button data-publish-button ${published ? 'disabled' : ''}>Publicar</button></form>
    </body>`, { url: 'https://clinic.test/admin/edit' });
    initEditor(dom.window.document);
    const document = dom.window.document;
    return { dom, document, window: dom.window, find: (selector) => document.querySelector(selector) };
}

test('editing, reverting and checkbox changes update save and publication state accurately', () => {
    const { window, find } = setup();
    const field = find('#name-es');
    assert.equal(find('[data-save-button]').disabled, true);
    field.value = 'Nueva consulta';
    field.dispatchEvent(new window.Event('input', { bubbles: true }));
    assert.equal(find('[data-save-button]').disabled, false);
    assert.equal(find('[data-publish-button]').disabled, true);
    const leaving = new window.Event('beforeunload', { cancelable: true });
    window.dispatchEvent(leaving);
    assert.equal(leaving.defaultPrevented, true);
    const publish = new window.Event('submit', { cancelable: true });
    find('[data-confirm]').dispatchEvent(publish);
    assert.equal(publish.defaultPrevented, true);
    field.value = 'Consulta';
    field.dispatchEvent(new window.Event('input', { bubbles: true }));
    assert.equal(find('[data-save-button]').disabled, true);
    assert.equal(find('[data-publish-button]').disabled, false);
    const leavingClean = new window.Event('beforeunload', { cancelable: true });
    window.dispatchEvent(leavingClean);
    assert.equal(leavingClean.defaultPrevented, false);
    find('[type=checkbox]').checked = false;
    find('[type=checkbox]').dispatchEvent(new window.Event('change', { bubbles: true }));
    assert.equal(find('[data-save-button]').disabled, false);
});

test('recovered validation input stays unsaved and an already published version stays disabled', () => {
    const recovered = setup({ recovered: true });
    assert.equal(recovered.find('[data-save-button]').disabled, false);
    assert.equal(recovered.find('[data-publish-button]').disabled, true);
    const { find, window } = setup({ published: true });
    find('#name-es').value = 'Changed';
    find('#name-es').dispatchEvent(new window.Event('input', { bubbles: true }));
    find('#name-es').value = 'Consulta';
    find('#name-es').dispatchEvent(new window.Event('input', { bubbles: true }));
    assert.equal(find('[data-publish-button]').disabled, true);
});

test('a saved version that is not on the site yet keeps saving enabled without blocking navigation', () => {
    const { window, find } = setup({ pending: true });
    assert.equal(find('[data-save-button]').disabled, false);
    assert.equal(find('[data-save-state]').textContent, 'Versión guardada, pendiente de publicar');
    assert.equal(find('[data-publish-button]').disabled, false);
    const leaving = new window.Event('beforeunload', { cancelable: true });
    window.dispatchEvent(leaving);
    assert.equal(leaving.defaultPrevented, false);
    find('#name-es').value = 'Changed';
    find('#name-es').dispatchEvent(new window.Event('input', { bubbles: true }));
    assert.equal(find('[data-save-state]').textContent, 'Cambios sin guardar');
    assert.equal(find('[data-publish-button]').disabled, true);
});

test('keyboard tabs retain both languages in submitted data and validation reveals the hidden language', async () => {
    const { window, document, find } = setup();
    assert.equal(find('#language-en').hidden, true);
    find('#tab-es').dispatchEvent(new window.KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
    assert.equal(document.activeElement.id, 'tab-en');
    assert.equal(find('#language-es').hidden, true);
    assert.equal(new window.FormData(find('[data-editor]')).get('payload[es][name]'), 'Consulta');
    find('#name-es').value = '';
    assert.equal(find('[data-editor]').checkValidity(), false);
    assert.equal(find('#language-es').hidden, false);
    await new Promise((resolve) => window.queueMicrotask(resolve));
    find('#name-es').value = 'Consulta';
    find('#description-en').value = '';
    assert.equal(find('[data-editor]').checkValidity(), false);
    assert.equal(find('#language-en').hidden, false);
});

test('error links focus the corresponding language and preserve literal error text', () => {
    const { find, document } = setup({ errors: '<div data-error-summary tabindex="-1"><span data-error-field="en.description">Falta &lt;texto&gt;</span></div>' });
    assert.equal(document.activeElement, find('[data-error-summary]'));
    find('[data-error-field] a').click();
    assert.equal(document.activeElement.id, 'description-en');
    assert.equal(find('#language-en').hidden, false);
    assert.equal(find('#description-en').getAttribute('aria-invalid'), 'true');
    assert.equal(find('[data-error-field] a').textContent, 'Falta <texto>');
    assert.match(find('#description-en').getAttribute('aria-describedby'), /error-message-0/);
    assert.match(find('#description-en').getAttribute('aria-describedby'), /character-count-0/);
});

test('saving blocks double submits and restores controls when returning with browser Back', () => {
    const { window, find } = setup();
    find('#name-es').value = 'Changed';
    find('#name-es').dispatchEvent(new window.Event('input', { bubbles: true }));
    const first = new window.Event('submit', { cancelable: true });
    find('[data-editor]').dispatchEvent(first);
    assert.equal(first.defaultPrevented, false);
    assert.equal(find('[data-save-button]').textContent, 'Guardando…');
    const second = new window.Event('submit', { cancelable: true });
    find('[data-editor]').dispatchEvent(second);
    assert.equal(second.defaultPrevented, true);
    window.dispatchEvent(new window.Event('pageshow'));
    assert.equal(find('[data-save-button]').disabled, false);
    assert.equal(find('[data-save-button]').textContent, 'Guardar y publicar');
    assert.ok(find('[data-save-button] svg'), 'The icon survives saving and browser Back');
});
