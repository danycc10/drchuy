import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initServiceImage } from '../../resources/js/service-image.js';

function setup() {
    const { window } = new JSDOM(`<form><input name="_token" value="token"><section data-service-image data-upload-url="/admin/media">
        <select data-media-select><option value="" data-src="/default.webp">Original</option><option value="5" data-src="/thumb.webp" data-display="/full.webp">Existing</option></select>
        <img data-crop-preview><input data-crop-axis="x" value="25"><input data-crop-axis="y" value="75">
        <div data-inline-upload hidden><input data-upload-title value="My image"><input type="file" data-upload-file><button type="button" data-inline-upload-button>Upload</button><p data-upload-status></p></div>
        </section></form>`);
    const { document } = window;
    window.XMLHttpRequest = undefined;
    Object.defineProperty(document.querySelector('[data-upload-file]'), 'files', { value: [new window.File(['image'], 'card.png', { type: 'image/png' })] });
    return { window, document };
}

test('inline upload keeps the editor on screen, blocks saving during upload and selects the private image', async () => {
    const { window, document } = setup();
    let resolve;
    let calls = 0;
    window.fetch = (url, options) => { calls++; assert.equal(options.body.get('title'), 'My image'); return new Promise((done) => { resolve = done; }); };
    initServiceImage(document);
    const button = document.querySelector('button');
    button.click(); button.click();
    const submit = new window.Event('submit', { cancelable: true });
    document.querySelector('form').dispatchEvent(submit);
    assert.equal(submit.defaultPrevented, true);
    assert.equal(calls, 1);
    resolve({ ok: true, json: async () => ({ id: 8, title: '<New image>', url: '/admin/media/8/display', thumb: '/admin/media/8/thumb' }) });
    await new Promise((done) => setTimeout(done, 0));
    assert.equal(document.querySelector('select').value, '8');
    assert.equal(document.querySelector('option:last-child').textContent, '<New image>');
    assert.equal(document.querySelector('[data-crop-preview]').getAttribute('src'), '/admin/media/8/display');
    assert.equal(document.querySelector('[data-crop-preview]').style.objectPosition, '50% 50%');
    assert.equal(button.disabled, false);
});

test('upload failure preserves the selection and allows retry; crop follows the chosen image', async () => {
    const { window, document } = setup();
    window.fetch = async () => ({ ok: false, status: 422, json: async () => ({ errors: { image: ['Invalid image'] } }) });
    initServiceImage(document);
    const select = document.querySelector('select');
    select.value = '5'; select.dispatchEvent(new window.Event('change'));
    assert.equal(document.querySelector('img').style.objectPosition, '25% 75%');
    document.querySelector('button').click();
    await new Promise((done) => setTimeout(done, 0));
    assert.equal(select.value, '5');
    assert.equal(document.querySelector('[data-upload-status]').textContent, 'Invalid image');
    assert.equal(document.querySelector('button').disabled, false);
    assert.equal(document.querySelector('[data-upload-title]').value, 'My image');
    assert.equal(document.querySelector('button').textContent, 'Reintentar carga');
    select.value = ''; select.dispatchEvent(new window.Event('change'));
    assert.equal(document.querySelector('img').style.objectPosition, '50% 50%');
});
