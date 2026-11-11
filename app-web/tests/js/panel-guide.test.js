import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initPanelGuide } from '../../resources/js/panel-guide.js';

function setup(url = 'https://clinic.test/admin') {
    const { window } = new JSDOM(`<button data-guide-open hidden>Open</button><section data-panel-guide data-guide-key="guide-user-7"><h2 tabindex="-1">Guide</h2><button data-guide-close hidden>Close</button>${['Edit', 'Save', 'Review', 'Publish'].map((title) => `<article data-guide-step><h3>${title}</h3></article>`).join('')}<div data-guide-navigation hidden><p data-guide-progress></p><button data-guide-previous>Previous</button><button data-guide-next>Next</button></div></section>`, { url });
    return { window, document: window.document };
}

test('guide can be completed, dismissed and reopened without losing focus or sharing preferences across accounts', () => {
    const { window, document } = setup();
    initPanelGuide(document);
    const next = document.querySelector('[data-guide-next]');
    assert.equal(document.querySelectorAll('[data-guide-step]:not([hidden])').length, 1);
    assert.equal(document.querySelector('[data-guide-previous]').disabled, true);
    next.click(); next.click(); next.click();
    assert.equal(next.textContent, 'Terminar guía');
    next.click();
    assert.equal(document.querySelector('[data-panel-guide]').hidden, true);
    assert.equal(window.localStorage.getItem('guide-user-7'), 'closed');
    assert.equal(window.localStorage.getItem('guide-user-8'), null);
    assert.equal(document.activeElement, document.querySelector('[data-guide-open]'));
    document.querySelector('[data-guide-open]').click();
    assert.equal(document.querySelector('[data-panel-guide]').hidden, false);
    assert.equal(document.activeElement, document.querySelector('h2'));
    assert.match(document.querySelector('[data-guide-progress]').textContent, /Paso 1/);
});

test('explicit help navigation reopens a dismissed guide and blocked storage does not break controls', () => {
    const first = setup(); first.window.localStorage.setItem('guide-user-7', 'closed'); initPanelGuide(first.document);
    assert.equal(first.document.querySelector('[data-panel-guide]').hidden, true);
    const second = setup('https://clinic.test/admin?guide=1'); second.window.localStorage.setItem('guide-user-7', 'closed'); initPanelGuide(second.document);
    assert.equal(second.document.querySelector('[data-panel-guide]').hidden, false);
    const third = setup(); Object.defineProperty(third.window, 'localStorage', { get() { throw new Error('Storage unavailable'); } });
    initPanelGuide(third.document); third.document.querySelector('[data-guide-close]').click(); third.document.querySelector('[data-guide-open]').click();
    assert.equal(third.document.querySelector('[data-panel-guide]').hidden, false);
});
