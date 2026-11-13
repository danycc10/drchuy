import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initImagePicker } from '../../resources/js/image-picker.js';

test('visual picker searches accents, preserves selection and keyboard focus, and incorporates uploaded images safely', () => {
    const { window } = new JSDOM(`<form><section data-service-image><p>Imagen actual: <strong data-image-selection></strong></p><div data-image-picker hidden><label data-image-search-field hidden><input data-image-search></label><div data-image-grid></div><p data-image-empty hidden></p><p data-image-count hidden></p></div><details data-image-native open><select name="image_id" data-media-select><option value="" data-src="/default.webp">Original</option><option value="2" data-src="/two.webp">Espirometría</option></select></details></section></form>`);
    const { document } = window;
    initImagePicker(document);
    assert.equal(document.querySelector('[data-image-picker]').hidden, false);
    assert.equal(document.querySelector('[data-image-native]').hidden, true, 'the dropdown gives way to the thumbnails');
    assert.equal(document.querySelector('[data-image-search-field]').hidden, true, 'two images need no search box');
    assert.equal(document.querySelector('[data-image-count]').hidden, true);
    assert.equal(document.querySelector('[data-image-selection]').textContent, 'Original');
    const input = document.querySelector('input');
    input.value = 'ESPIROMETRIA'; input.dispatchEvent(new window.Event('input'));
    const tile = document.querySelector('[data-image-value="2"]');
    assert.equal(tile.hidden, false);
    assert.equal(document.querySelector('[data-image-value=""]').hidden, true);
    tile.focus(); tile.click();
    assert.equal(document.activeElement, tile);
    assert.equal(new window.FormData(document.querySelector('form')).get('image_id'), '2');
    assert.equal(tile.getAttribute('aria-pressed'), 'true');
    input.value = 'missing'; input.dispatchEvent(new window.Event('input'));
    assert.equal(document.querySelector('[data-image-empty]').hidden, false);
    assert.equal(document.querySelector('[data-image-count]').hidden, false);
    assert.equal(document.querySelector('[data-image-count]').textContent, '0 de 2 imágenes');
    assert.equal(document.querySelector('[data-image-selection]').textContent, 'Espirometría');
    const option = new window.Option('<script>unsafe</script>', '3', false, true);
    option.dataset.src = '/three.webp'; document.querySelector('select').add(option);
    document.querySelector('section').dispatchEvent(new window.Event('image:uploaded'));
    assert.equal(input.value, '');
    assert.equal(document.querySelector('[data-image-value="3"]').getAttribute('aria-pressed'), 'true');
    assert.equal(document.querySelector('script'), null);
    const enter = new window.KeyboardEvent('keydown', { key: 'Enter', cancelable: true });
    input.dispatchEvent(enter); assert.equal(enter.defaultPrevented, true);
});

test('search box appears only once the library holds more than eight images', () => {
    const options = Array.from({ length: 9 }, (_, i) => `<option value="${i + 1}" data-src="/${i + 1}.webp">Foto ${i + 1}</option>`).join('');
    const { window } = new JSDOM(`<form><section data-service-image><div data-image-picker hidden><label data-image-search-field hidden><input data-image-search></label><div data-image-grid></div><p data-image-empty hidden></p><p data-image-count hidden></p></div><details data-image-native open><select name="image_id" data-media-select><option value="" data-src="/default.webp">Original</option>${options}</select></details></section></form>`);
    initImagePicker(window.document);
    assert.equal(window.document.querySelector('[data-image-search-field]').hidden, false);
    assert.equal(window.document.querySelectorAll('.image-picker-option').length, 10);
});
