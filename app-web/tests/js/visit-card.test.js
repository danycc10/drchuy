import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initVisitCard } from '../../resources/js/visit-card.js';

function setup() {
    const { window } = new JSDOM(`<article data-visit-card>
        <address data-visit-address tabindex="-1">Calle &lt;Principal&gt; 100<br>Piedras Negras, Coahuila</address>
        <button data-copy-address hidden data-success="Copiado" data-fallback="Copia manual">Copiar</button>
        <p data-copy-status role="status"></p>
        <button data-print-visit hidden data-error="Usa el navegador">Imprimir</button></article>`);
    const find = (selector) => window.document.querySelector(selector);
    return { window, document: window.document, find };
}
const flush = () => new Promise((resolve) => setImmediate(resolve));

test('copying preserves address line breaks and literal text, announces success and prevents duplicate writes', async () => {
    const { window, document, find } = setup();
    const copied = [];
    let resolve;
    Object.defineProperty(window.navigator, 'clipboard', { value: { writeText: (text) => { copied.push(text); return new Promise((done) => { resolve = done; }); } } });
    initVisitCard(document);
    const button = find('[data-copy-address]');
    assert.equal(button.hidden, false);
    button.focus();
    button.click();
    button.click();
    assert.equal(button.disabled, true);
    assert.deepEqual(copied, ['Calle <Principal> 100\nPiedras Negras, Coahuila']);
    resolve();
    await flush();
    assert.equal(button.disabled, false);
    assert.equal(find('[data-copy-status]').textContent, 'Copiado');
    assert.equal(document.activeElement, button);
});

test('clipboard rejection and missing support select the address for manual copying without claiming success', async () => {
    for (const rejected of [false, true]) {
        const { window, document, find } = setup();
        if (rejected) Object.defineProperty(window.navigator, 'clipboard', { value: { writeText: async () => { throw new Error('Permission denied'); } } });
        initVisitCard(document);
        find('[data-copy-address]').click();
        await flush();
        assert.equal(find('[data-copy-status]').textContent, 'Copia manual');
        assert.equal(document.activeElement, find('address'));
        assert.ok(window.getSelection().toString().includes('Piedras Negras'));
        assert.equal(find('[data-copy-address]').disabled, false);
    }
});

test('print mode starts only on request and clears after print or browser Back', () => {
    const { window, document, find } = setup();
    let printed = 0;
    window.print = () => { assert.equal(document.body.classList.contains('printing-visit'), true); printed++; };
    initVisitCard(document);
    assert.equal(printed, 0);
    assert.equal(find('[data-print-visit]').hidden, false);
    find('[data-print-visit]').click();
    assert.equal(printed, 1);
    window.dispatchEvent(new window.Event('afterprint'));
    assert.equal(document.body.classList.contains('printing-visit'), false);
    find('[data-print-visit]').click();
    window.dispatchEvent(new window.Event('pageshow'));
    assert.equal(document.body.classList.contains('printing-visit'), false);
});

test('printing failure recovers and pages without a visit card remain usable', () => {
    const { window, document, find } = setup();
    window.print = () => { throw new Error('Unavailable'); };
    initVisitCard(document);
    find('[data-print-visit]').click();
    assert.equal(document.body.classList.contains('printing-visit'), false);
    assert.equal(find('[data-copy-status]').textContent, 'Usa el navegador');
    const other = new JSDOM('<main>Privacidad</main>');
    assert.doesNotThrow(() => initVisitCard(other.window.document));
});
