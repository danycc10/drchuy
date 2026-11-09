import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initPublicMotion } from '../../resources/js/public-motion.js';

function setup({ reduced = false, hash = '', animate = true } = {}) {
    const { window } = new JSDOM(`<body><div class="location-bar"></div><header class="site-header"></header>
        <div class="hero-copy"><span class="eyebrow">Care</span><h1>Respiratory care</h1><p class="hero-intro">Welcome</p><a href="#hours" id="appointment">Book</a></div><div class="hero-image"></div>
        <section class="care-inner"><a href="#hours" id="care-link">Hours</a></section>
        <div class="office-gallery"><figure>Photo one</figure><figure>Photo two</figure></div>
        <div id="hours"></div></body>`, { url: `https://clinic.test/es${hash}` });
    const observers = [];
    window.IntersectionObserver = class {
        constructor(callback) { this.callback = callback; this.targets = new Set(); observers.push(this); }
        observe(target) { this.targets.add(target); }
        unobserve(target) { this.targets.delete(target); }
        disconnect() { this.disconnected = true; this.targets.clear(); }
        fire(target, isIntersecting = true) { if (!this.disconnected) this.callback([{ target, isIntersecting }]); }
    };
    let preferenceChanged;
    const preference = { matches: reduced, addEventListener: (_, listener) => { preferenceChanged = listener; } };
    window.matchMedia = () => preference;
    const animations = [];
    if (animate) window.Element.prototype.animate = function (frames, options) {
        const animation = { target: this, frames, options, cancel() { this.cancelled = true; this.oncancel?.(); } };
        animations.push(animation);
        return animation;
    };
    initPublicMotion(window.document);
    return { window, document: window.document, observers, animations, reduce: () => {
        preference.matches = true;
        preferenceChanged({ matches: true });
    } };
}

test('scroll entrances run once and leave the appointment action immediately available', () => {
    const { document, observers, animations } = setup();
    assert.equal(animations.length, 4);
    assert.equal(animations.some((animation) => animation.target.id === 'appointment'), false);
    const section = document.querySelector('.care-inner');
    observers[1].fire(section, false);
    assert.equal(animations.length, 4);
    observers[1].fire(section);
    assert.equal(animations.length, 5);
    assert.equal(observers[1].targets.has(section), false);
    observers[1].fire(section);
    assert.equal(animations.length, 5);
    assert.equal(section.hasAttribute('hidden'), false);
    assert.equal(section.hasAttribute('style'), false);
    observers[0].fire(document.querySelector('.location-bar'), false);
    assert.equal(document.querySelector('header').classList.contains('is-scrolled'), true);
});

test('reduced motion skips entrances and a preference change cancels current animations', () => {
    const reduced = setup({ reduced: true });
    assert.equal(reduced.animations.length, 0);
    assert.equal(reduced.observers.length, 1);
    const active = setup();
    active.reduce();
    assert.equal(active.animations.every((animation) => animation.cancelled), true);
    assert.equal(active.observers[1].disconnected, true);
});

test('keyboard focus and anchor navigation finish motion without hiding content', () => {
    const { document, window, observers, animations } = setup();
    const section = document.querySelector('.care-inner');
    observers[1].fire(section);
    document.querySelector('#care-link').focus();
    assert.equal(animations.find((animation) => animation.target === section).cancelled, true);
    window.dispatchEvent(new window.Event('hashchange'));
    assert.equal(animations.every((animation) => animation.cancelled), true);
    assert.equal(observers[1].disconnected, true);
});

test('deep links and browsers without animation support keep all content usable', () => {
    const deepLink = setup({ hash: '#hours' });
    assert.equal(deepLink.animations.length, 0);
    const unsupported = setup({ animate: false });
    assert.equal(unsupported.animations.length, 0);
    assert.equal(unsupported.document.querySelectorAll('[hidden], [inert]').length, 0);
    assert.equal(unsupported.document.querySelector('#appointment').getAttribute('href'), '#hours');
});
