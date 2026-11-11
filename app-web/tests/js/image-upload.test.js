import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { uploadImage } from '../../resources/js/image-upload.js';

function setup() {
    const { window } = new JSDOM('');
    let xhr;
    window.XMLHttpRequest = class extends window.EventTarget {
        constructor() { super(); xhr = this; this.upload = new window.EventTarget(); }
        open(method, url) { this.method = method; this.url = url; }
        setRequestHeader(name, value) { this.header = [name, value]; }
        send(body) { this.body = body; }
        abort() { this.dispatchEvent(new window.Event('abort')); }
    };
    return { window, xhr: () => xhr };
}

test('upload reports transferred bytes, preserves the CSRF form and parses the server confirmation', async () => {
    const { window, xhr } = setup();
    const body = new window.FormData(); body.set('_token', 'test-token');
    const progress = [];
    const result = uploadImage(window, '/admin/media', body, (value) => progress.push(value), new window.AbortController().signal);
    xhr().upload.dispatchEvent(new window.ProgressEvent('progress', { lengthComputable: true, loaded: 25, total: 100 }));
    xhr().upload.dispatchEvent(new window.ProgressEvent('progress', { lengthComputable: true, loaded: 100, total: 100 }));
    assert.deepEqual(progress, [25, 100]);
    assert.equal(xhr().body.get('_token'), 'test-token');
    assert.equal(xhr().timeout, 120000);
    xhr().status = 201; xhr().responseText = '{"id":7}'; xhr().dispatchEvent(new window.Event('load'));
    const response = await result;
    assert.equal(response.ok, true); assert.deepEqual(await response.json(), { id: 7 });
});

test('timeout and abort settle the request without claiming success and allow a fresh retry', async () => {
    const { window, xhr } = setup();
    let result = uploadImage(window, '/admin/media', new window.FormData(), () => {}, new window.AbortController().signal);
    xhr().dispatchEvent(new window.Event('timeout'));
    await assert.rejects(result, /biblioteca/);
    const controller = new window.AbortController();
    result = uploadImage(window, '/admin/media', new window.FormData(), () => {}, controller.signal);
    controller.abort();
    await assert.rejects(result, { name: 'AbortError' });
});
