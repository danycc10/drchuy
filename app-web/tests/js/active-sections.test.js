import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initActiveSections } from '../../resources/js/active-sections.js';

function setup({ observers = true, reverse = false } = {}) {
    const { window } = new JSDOM(`<header class="site-header"><nav id="site-navigation">
        <a href="#services">Servicios</a>${reverse ? '<a href="#hours">Horarios</a><a href="#location">Ubicación</a>' : '<a href="#location">Ubicación</a><a href="#hours">Horarios</a>'}
        <a href="#missing">No disponible</a></nav></header>
        <input id="search"><section id="services"></section><section id="location"><div id="hours"></div></section>`, { url: 'https://clinic.test/#services' });
    const document = window.document;
    const geometry = { header: [0, 98], services: [300, 1000], location: [1500, 2300], hours: [1650, 2100] };
    let frame;
    let resize;
    const observed = [];
    window.requestAnimationFrame = (callback) => { frame = callback; };
    if (observers) window.ResizeObserver = class {
        constructor(callback) { resize = callback; }
        observe(element) { observed.push(element); }
    };
    for (const [id, selector] of Object.entries({ header: '.site-header', services: '#services', location: '#location', hours: '#hours' })) {
        document.querySelector(selector).getBoundingClientRect = () => {
            const [top, bottom] = geometry[id];
            return { top, bottom, height: bottom - top };
        };
    }
    initActiveSections(document);
    const flush = () => { const callback = frame; frame = null; callback?.(); };
    return {
        window, document, geometry, observed, flush,
        resize: () => { resize(); flush(); },
        scroll: () => { window.dispatchEvent(new window.Event('scroll')); flush(); },
        active: () => [...document.querySelectorAll('[aria-current=location]')].map((link) => link.hash),
    };
}

test('scrolling marks only the visible section without changing focus, hash or history', () => {
    const { document, window, geometry, scroll, active } = setup();
    assert.deepEqual(active(), [], 'The hero has no falsely active menu destination');
    document.getElementById('search').focus();
    const historyLength = window.history.length;
    geometry.services = [100, 800];
    scroll();
    assert.deepEqual(active(), ['#services']);
    geometry.services = [-900, -200];
    geometry.location = [100, 900];
    scroll();
    assert.deepEqual(active(), ['#location']);
    assert.equal(window.location.href, 'https://clinic.test/#services');
    assert.equal(window.history.length, historyLength);
    assert.equal(document.activeElement.id, 'search');
    geometry.location = [-1000, -100];
    scroll();
    assert.deepEqual(active(), [], 'The footer does not keep a stale section selected');
});

test('nested hours take precedence regardless of menu order and return to contact after leaving them', () => {
    for (const reverse of [false, true]) {
        const { geometry, scroll, active } = setup({ reverse });
        geometry.location = [-100, 900];
        geometry.hours = [110, 600];
        scroll();
        assert.deepEqual(active(), ['#hours']);
        geometry.hours = [-500, 50];
        scroll();
        assert.deepEqual(active(), ['#location']);
    }
});

test('header resizing updates anchor clearance and reevaluates sections after layout changes', () => {
    const { geometry, document, observed, resize, active } = setup();
    assert.equal(document.documentElement.style.getPropertyValue('--anchor-offset'), '114px');
    assert.equal(observed.length, 4);
    geometry.header = [0, 170];
    geometry.services = [180, 900];
    resize();
    assert.equal(document.documentElement.style.getPropertyValue('--anchor-offset'), '186px');
    assert.deepEqual(active(), ['#services']);
    geometry.services = [200, 900];
    resize();
    assert.deepEqual(active(), []);
});

test('restored pages and layout toggles work without ResizeObserver; pages without navigation stay untouched', () => {
    const { window, document, geometry, flush, active } = setup({ observers: false });
    geometry.location = [100, 900];
    window.dispatchEvent(new window.Event('pageshow'));
    flush();
    assert.deepEqual(active(), ['#location']);
    geometry.hours = [100, 500];
    document.getElementById('hours').dispatchEvent(new window.Event('toggle'));
    flush();
    assert.deepEqual(active(), ['#hours']);
    const other = new JSDOM('<main>Privacidad</main>');
    assert.doesNotThrow(() => initActiveSections(other.window.document));
    assert.equal(other.window.document.documentElement.hasAttribute('style'), false);
});
