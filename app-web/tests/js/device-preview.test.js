import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initDevicePreview } from '../../resources/js/device-preview.js';

test('device sizes resize the private iframe without reloading its content or moving focus', () => {
    const { window } = new JSDOM('<section data-device-preview><div data-device-buttons hidden><button data-preview-width="390" aria-pressed="true">Mobile</button><button data-preview-width="1280" aria-pressed="false">Desktop</button><button data-preview-refresh>Refresh</button></div><p data-preview-status></p><iframe data-preview-frame src="/admin/preview/es" width="390"></iframe></section>');
    const { document } = window;
    initDevicePreview(document);
    const button = document.querySelector('[data-preview-width="1280"]');
    const frame = document.querySelector('iframe');
    const content = frame.contentDocument;
    button.focus(); button.click();
    assert.equal(frame.width, '1280');
    assert.equal(frame.contentDocument, content);
    assert.equal(document.activeElement, button);
    assert.equal(button.getAttribute('aria-pressed'), 'true');
    document.querySelector('[data-preview-width="390"]').click();
    assert.equal(frame.width, '390');
    assert.equal(button.getAttribute('aria-pressed'), 'false');
    document.querySelector('[data-preview-refresh]').click();
    assert.equal(frame.getAttribute('src'), '/admin/preview/es');
});
