import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initPublicNavigation, initServiceSearch } from '../../resources/js/public-site.js';

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

test('language selection submits a known section without adding the language to the URL', () => {
    const { window } = new JSDOM(`<form method="post" action="/idioma" data-language-form>
        <input name="locale" value="en"><input name="page" value="home"><input name="anchor" value="">
        <button>EN</button></form>`, { url: 'https://clinic.test/#hours' });
    const { document } = window;
    initPublicNavigation(document);
    const form = document.querySelector('form');
    assert.equal(form.elements.anchor.value, 'hours');
    assert.equal(window.location.pathname, '/');
    assert.equal(window.location.search, '');
    window.location.hash = '#unknown';
    form.dispatchEvent(new window.Event('submit'));
    assert.equal(form.elements.anchor.value, '');
    assert.equal(form.elements.locale.value, 'en');
});

function searchableCatalog() {
    const { window } = new JSDOM(`<body><div data-service-search hidden>
        <input type="search" data-service-query><button data-clear-service-search>Clear</button>
        <p data-service-count data-template="Services found: {count} of {total}"></p></div>
        <div data-service-empty hidden><button data-clear-service-search>Show all</button></div>
        <div data-service-group><h3>Estudios respiratorios</h3><details data-service-item id="spirometry"><summary>Espirometría</summary><div class="service-description"><p>Medición de la función pulmonar.</p></div></details></div>
        <div data-service-group><h3>Consultas</h3><details data-service-item id="consultation" open><summary>Consulta médica</summary><div class="service-description"><p>Valoración individual.</p></div></details></div></body>`);
    const { document } = window;
    initServiceSearch(document);
    return { window, document, find: (selector) => document.querySelector(selector), search: (value) => {
        const input = document.querySelector('input');
        input.value = value;
        input.dispatchEvent(new window.Event('input'));
    } };
}

test('service search matches accents, case, description and category without losing expanded items', () => {
    const { search, find } = searchableCatalog();
    assert.equal(find('[data-service-search]').hidden, false);
    search('  ESPIROMETRIA  ');
    assert.equal(find('#spirometry').hidden, false);
    assert.equal(find('#spirometry').open, true);
    assert.equal(find('#consultation').hidden, true);
    assert.equal(find('#consultation').closest('[data-service-group]').hidden, true);
    assert.equal(find('[data-service-count]').textContent, 'Services found: 1 of 2');
    search('respiratorios funcion');
    assert.equal(find('#spirometry').hidden, false);
    find('[data-clear-service-search]').click();
    assert.equal(find('#spirometry').open, false);
    assert.equal(find('#consultation').open, true);
    assert.equal(find('#consultation').hidden, false);
    assert.equal(find('#consultation').closest('[data-service-group]').hidden, false);
});

test('no-results recovery and Escape restore all services and return focus to the query', () => {
    const { search, find, document, window } = searchableCatalog();
    search('<script>');
    assert.equal(find('[data-service-empty]').hidden, false);
    assert.equal(find('[data-service-count]').textContent, 'Services found: 0 of 2');
    find('[data-service-empty] button').click();
    assert.equal(find('[data-service-empty]').hidden, true);
    assert.equal(document.activeElement, find('input'));
    search('consulta');
    find('input').dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Escape', cancelable: true }));
    assert.equal(find('input').value, '');
    assert.equal(find('[data-service-count]').textContent, 'Services found: 2 of 2');
});
