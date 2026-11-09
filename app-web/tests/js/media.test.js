import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initMedia } from '../../resources/js/media.js';

test('choosing an image updates its preview and leaves the selected value for draft submission', () => {
    const { window } = new JSDOM(`<select data-media-select data-preview-id="preview" name="payload[shared][doctor_image_id]">
        <option value="" data-src="/images/doctor.webp">Original</option><option value="42" data-src="/admin/media/42/image/thumb">New photo</option></select><img id="preview" src="/images/doctor.webp">`, { url: 'https://clinic.test/admin' });
    initMedia(window.document);
    const select = window.document.querySelector('select');
    select.value = '42';
    select.dispatchEvent(new window.Event('change'));
    assert.equal(window.document.querySelector('img').getAttribute('src'), '/admin/media/42/image/thumb');
    assert.equal(select.value, '42');
    select.value = '';
    select.dispatchEvent(new window.Event('change'));
    assert.equal(window.document.querySelector('img').getAttribute('src'), '/images/doctor.webp');
});

test('uploads prevent duplicate submissions and recover controls on browser Back', () => {
    const { window } = new JSDOM('<form data-media-upload><button data-upload-button><svg></svg><span data-upload-label>Subir fotografía</span></button></form>');
    const { document } = window;
    initMedia(document);
    const form = document.querySelector('form');
    form.dispatchEvent(new window.Event('submit', { cancelable: true }));
    assert.equal(document.querySelector('button').disabled, true);
    assert.equal(form.getAttribute('aria-busy'), 'true');
    const duplicate = new window.Event('submit', { cancelable: true });
    form.dispatchEvent(duplicate);
    assert.equal(duplicate.defaultPrevented, true);
    window.dispatchEvent(new window.Event('pageshow'));
    assert.equal(document.querySelector('button').disabled, false);
    assert.equal(form.hasAttribute('aria-busy'), false);
    assert.ok(document.querySelector('svg'));
});
