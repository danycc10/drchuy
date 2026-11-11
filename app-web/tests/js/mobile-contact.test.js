import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initMobileContact } from '../../resources/js/mobile-contact.js';

test('contact clearance follows bar height and search focus, with resize and page-return fallback', async () => {
    const { window } = new JSDOM('<body><input data-service-query><button>Next</button><nav class="mobile-contact"></nav></body>');
    const { document } = window;
    let height = 84;
    document.querySelector('nav').getBoundingClientRect = () => ({ height: document.body.classList.contains('search-is-focused') ? 0 : height });
    initMobileContact(document);
    const clearance = () => document.documentElement.style.getPropertyValue('--mobile-contact-height');
    assert.equal(clearance(), '84px');
    height = 110;
    window.dispatchEvent(new window.Event('resize'));
    assert.equal(clearance(), '110px');
    document.querySelector('input').focus();
    assert.equal(clearance(), '0px');
    document.querySelector('button').focus();
    await Promise.resolve();
    assert.equal(clearance(), '110px');
    assert.equal(document.body.classList.contains('search-is-focused'), false);
    height = 72;
    window.dispatchEvent(new window.Event('pageshow'));
    assert.equal(clearance(), '72px');
});
