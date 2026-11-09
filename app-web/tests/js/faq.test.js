import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initFaq } from '../../resources/js/faq.js';
import { initEditor } from '../../resources/js/editor.js';

function row(index, text = '') {
    return `<fieldset data-faq-item><legend data-faq-number></legend><div data-faq-actions hidden>
        <button type="button" data-faq-up>Subir</button><button type="button" data-faq-down>Bajar</button><button type="button" data-faq-remove>Quitar</button></div>
        <input type="hidden" name="payload[items][${index}][visible]" value="0"><input type="checkbox" name="payload[items][${index}][visible]" value="1" checked>
        <label for="q-${index}">ES<input id="q-${index}" name="payload[items][${index}][es][question]" value="${text}"></label>
        <textarea name="payload[items][${index}][en][answer]">${text} EN</textarea></fieldset>`;
}

function setup(count = 2) {
    const { window } = new JSDOM(`<button data-save-button></button><button data-publish-button></button><p data-save-state></p>
        <form data-editor><section data-faq-editor><div data-faq-items>${Array.from({ length: count }, (_, i) => row(i, `Question ${i}`)).join('')}</div>
        <p data-faq-empty hidden></p><button type="button" data-faq-add hidden>Add</button><p data-faq-status role="status"></p>
        <template data-faq-template>${row('__INDEX__')}</template></section></form>`, { url: 'https://clinic.test/admin/content' });
    window.confirm = () => true;
    initFaq(window.document);
    initEditor(window.document);
    return { window, document: window.document, find: (selector) => window.document.querySelector(selector), rows: () => [...window.document.querySelectorAll('[data-faq-item]')] };
}

test('reordering keeps translations paired, submits sequential positions and marks the draft dirty', () => {
    const { window, find, rows, document } = setup();
    assert.equal(find('[data-save-button]').disabled, true);
    assert.equal(rows()[0].querySelector('[data-faq-up]').disabled, true);
    rows()[1].querySelector('[data-faq-up]').click();
    const data = new window.FormData(find('form'));
    assert.equal(data.get('payload[items][0][es][question]'), 'Question 1');
    assert.equal(data.get('payload[items][0][en][answer]'), 'Question 1 EN');
    assert.equal(data.get('payload[items][1][es][question]'), 'Question 0');
    assert.equal(find('[data-save-button]').disabled, false);
    assert.equal(find('[data-publish-button]').disabled, true);
    assert.equal(document.activeElement.value, 'Question 1');
    rows()[0].querySelector('[data-faq-down]').click();
    assert.equal(find('[data-save-button]').disabled, true);
});

test('removal supports cancellation, focuses a neighbor and adding reuses no existing field identifiers', () => {
    const { window, find, rows, document } = setup();
    window.confirm = () => false;
    rows()[0].querySelector('[data-faq-remove]').click();
    assert.equal(rows().length, 2);
    window.confirm = () => true;
    rows()[0].querySelector('[data-faq-remove]').click();
    assert.equal(rows().length, 1);
    assert.equal(document.activeElement.value, 'Question 1');
    find('[data-faq-add]').click();
    assert.equal(rows().length, 2);
    assert.equal(document.activeElement.id, 'q-2');
    assert.equal(rows()[1].querySelector('label').htmlFor, 'q-2');
    assert.ok(new window.FormData(find('form')).has('payload[items][1][es][question]'));
    while (rows().length) rows()[0].querySelector('[data-faq-remove]').click();
    assert.equal(find('[data-faq-empty]').hidden, false);
    assert.equal(document.activeElement, find('[data-faq-add]'));
    assert.equal(new window.FormData(find('form')).has('payload[items][0][es][question]'), false);
});

test('the twelve question limit recovers after removing a question', () => {
    const { find, rows } = setup(12);
    assert.equal(find('[data-faq-add]').disabled, true);
    find('[data-faq-add]').click();
    assert.equal(rows().length, 12);
    rows()[5].querySelector('[data-faq-remove]').click();
    assert.equal(find('[data-faq-add]').disabled, false);
    find('[data-faq-add]').click();
    assert.equal(rows().length, 12);
    assert.equal(find('[data-faq-add]').disabled, true);
    assert.match(find('[data-faq-status]').textContent, /12 de 12/);
});
