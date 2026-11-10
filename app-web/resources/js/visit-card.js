export function initVisitCard(document) {
    const card = document.querySelector('[data-visit-card]');
    if (!card) return;
    const window = document.defaultView;
    const address = card.querySelector('[data-visit-address]');
    const copy = card.querySelector('[data-copy-address]');
    const status = card.querySelector('[data-copy-status]');
    const print = card.querySelector('[data-print-visit]');
    const clearPrinting = () => document.body.classList.remove('printing-visit');
    if (copy && address) {
        copy.hidden = false;
        copy.addEventListener('click', async () => {
            if (copy.disabled) return;
            const wasFocused = document.activeElement === copy;
            copy.disabled = true;
            copy.setAttribute('aria-busy', 'true');
            status.textContent = '';
            const text = [...address.childNodes].map((node) => node.nodeName === 'BR' ? '\n' : node.textContent).join('').trim();
            try {
                if (!window.navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                await window.navigator.clipboard.writeText(text);
                status.textContent = copy.dataset.success;
            } catch {
                address.focus({ preventScroll: true });
                const range = document.createRange();
                range.selectNodeContents(address);
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                status.textContent = copy.dataset.fallback;
            } finally {
                copy.disabled = false;
                copy.removeAttribute('aria-busy');
                if (wasFocused && document.activeElement === document.body) copy.focus({ preventScroll: true });
            }
        });
    }
    if (print && typeof window.print === 'function') {
        print.hidden = false;
        print.addEventListener('click', () => {
            document.body.classList.add('printing-visit');
            try { window.print(); }
            catch { clearPrinting(); status.textContent = print.dataset.error; }
        });
    }
    window.addEventListener('afterprint', clearPrinting);
    window.addEventListener('pageshow', clearPrinting);
}
