import test from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { initServiceOrder } from '../../resources/js/service-order.js';

test('reordering with buttons and drag changes submitted order, preserves revision pairing and announces position', () => {
    const { window } = new JSDOM(`<form data-service-order><ol data-order-list>${[1, 2, 3].map((id) => `<li data-order-item><input name="order[]" value="${id}"><input name="revisions[${id}]" value="${id + 10}"><span class="order-number"></span><strong data-order-name>Service ${id}</strong><div data-order-controls hidden><button type="button" data-order-up>Up</button><button type="button" data-order-down>Down</button></div></li>`).join('')}</ol><p data-order-status></p></form>`);
    const { document } = window;
    initServiceOrder(document);
    const form = document.querySelector('form');
    const order = () => new window.FormData(form).getAll('order[]');
    const first = document.querySelector('li');
    first.querySelector('[data-order-down]').click();
    assert.deepEqual(order(), ['2', '1', '3']);
    assert.equal(new window.FormData(form).get('revisions[1]'), '11');
    first.querySelector('[data-order-up]').click();
    assert.deepEqual(order(), ['1', '2', '3']);
    assert.equal(document.activeElement, first.querySelector('[data-order-down]'));
    const drag = new window.Event('dragstart', { bubbles: true });
    drag.dataTransfer = { setData() {} };
    first.dispatchEvent(drag);
    document.querySelector('li:last-child').dispatchEvent(new window.Event('drop', { bubbles: true, cancelable: true }));
    assert.deepEqual(order(), ['2', '3', '1']);
    assert.match(document.querySelector('[data-order-status]').textContent, /posición 3/);
});
