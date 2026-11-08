import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initPublicNavigation } from '../../resources/js/public-site.js';

test('public menu closes with Escape, outside clicks and section navigation', () => {
    const { window } = new JSDOM(`<body><button class="nav-toggle" aria-expanded="false">Menú</button>
        <nav id="site-navigation"><a href="#hours">Horarios</a></nav>
        <a data-language-switch href="/en">EN</a><section id="hours"></section><button id="outside">Outside</button></body>`, { url: 'https://clinic.test/es#hours' });
    window.matchMedia = () => ({ matches: true, addEventListener() {} });
    const { document } = window;
    const menu = document.querySelector('nav');
    const toggle = document.querySelector('.nav-toggle');
    initPublicNavigation(document);
    assert.equal(document.querySelector('[data-language-switch]').hash, '#hours');
    toggle.click();
    assert.equal(toggle.getAttribute('aria-expanded'), 'true');
    document.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Escape' }));
    assert.equal(document.activeElement, toggle);
    assert.equal(menu.classList.contains('open'), false);
    toggle.click();
    menu.querySelector('a').click();
    assert.equal(document.activeElement.id, 'hours');
    assert.equal(toggle.getAttribute('aria-expanded'), 'false');
    toggle.click();
    document.querySelector('#outside').click();
    assert.equal(menu.classList.contains('open'), false);
});
