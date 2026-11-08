import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initNavigation } from '../../resources/js/navigation.js';

function setup(isMobile = true) {
    const dom = new JSDOM(`<!doctype html><body>
        <a class="skip-link" href="#main">Ir al contenido</a>
        <button data-nav-backdrop hidden tabindex="-1"></button>
        <aside id="admin-navigation"><button data-menu-close>Cerrar menú</button><a href="#main" aria-current="page">Resumen</a><a href="#services">Servicios</a></aside>
        <div class="admin-workspace"><button data-menu-toggle aria-expanded="false">Menú</button><main id="main"></main></div>
    </body>`, { url: 'https://clinic.test/admin' });
    const { document } = dom.window;
    let onResize;
    const media = { matches: isMobile, addEventListener: (_, listener) => { onResize = listener; } };
    dom.window.matchMedia = () => media;
    initNavigation(document);
    return {
        document, window: dom.window, find: (selector) => document.querySelector(selector),
        resize: (mobile) => { media.matches = mobile; onResize(); },
    };
}

test('mobile navigation contains keyboard focus and Escape restores the menu button', () => {
    const { document, window, find } = setup();
    assert.equal(find('#admin-navigation').inert, true);
    find('[data-menu-toggle]').click();
    assert.equal(find('#admin-navigation').inert, false);
    assert.equal(find('.admin-workspace').inert, true);
    assert.equal(find('.skip-link').inert, true);
    assert.equal(find('[data-nav-backdrop]').hidden, false);
    assert.equal(document.activeElement, find('[data-menu-close]'));
    assert.equal(find('#admin-navigation').getAttribute('aria-modal'), 'true');
    document.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Tab', shiftKey: true, cancelable: true }));
    assert.equal(document.activeElement, find('a[href="#services"]'));
    document.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Tab', cancelable: true }));
    assert.equal(document.activeElement, find('[data-menu-close]'));
    document.dispatchEvent(new window.KeyboardEvent('keydown', { key: 'Escape', cancelable: true }));
    assert.equal(document.activeElement, find('[data-menu-toggle]'));
    assert.equal(find('.admin-workspace').inert, false);
    assert.equal(find('#admin-navigation').inert, true);
    assert.equal(find('[data-menu-toggle]').getAttribute('aria-expanded'), 'false');
});

test('backdrop, close button and navigation links dismiss the mobile menu', () => {
    const { find, document } = setup();
    for (const selector of ['[data-nav-backdrop]', '[data-menu-close]', 'a[href="#services"]']) {
        find('[data-menu-toggle]').click();
        find(selector).click();
        assert.equal(find('[data-nav-backdrop]').hidden, true);
        assert.equal(document.body.classList.contains('nav-open'), false);
        assert.equal(find('.admin-workspace').inert, false);
    }
});

test('resizing restores desktop access and prevents focus in a collapsed mobile menu', () => {
    const { find, document, resize } = setup();
    find('[data-menu-toggle]').click();
    resize(false);
    assert.equal(find('#admin-navigation').inert, false);
    assert.equal(find('.admin-workspace').inert, false);
    assert.equal(find('#admin-navigation').hasAttribute('aria-modal'), false);
    assert.equal(document.activeElement, find('[aria-current=page]'));
    resize(true);
    assert.equal(find('#admin-navigation').inert, true);
    assert.equal(document.activeElement, find('[data-menu-toggle]'));
});
