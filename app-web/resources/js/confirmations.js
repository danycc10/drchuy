const activeDialogs = new WeakMap();

export function confirmAction(document, { title = 'Confirmar acción', message, detail = '', label = 'Confirmar' }, trigger = document.activeElement) {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (!dialog?.showModal) return Promise.resolve(document.defaultView.confirm([message, detail].filter(Boolean).join('\n\n')));
    if (activeDialogs.has(document)) return Promise.resolve(false);

    return new Promise((resolve) => {
        const cancel = dialog.querySelector('[data-confirm-cancel]');
        const accept = dialog.querySelector('[data-confirm-accept]');
        dialog.querySelector('[data-confirm-title]').textContent = title;
        dialog.querySelector('[data-confirm-message]').textContent = message;
        const context = dialog.querySelector('[data-confirm-detail]');
        context.textContent = detail;
        context.hidden = !detail;
        accept.textContent = label;
        let settled = false;
        const finish = (confirmed, restoreFocus = true) => {
            if (settled) return;
            settled = true;
            cancel.removeEventListener('click', onCancel);
            accept.removeEventListener('click', onAccept);
            dialog.removeEventListener('cancel', onCancel);
            dialog.removeEventListener('close', onClose);
            document.defaultView.removeEventListener('pagehide', onLeave);
            if (dialog.open) dialog.close();
            activeDialogs.delete(document);
            if (restoreFocus && trigger?.isConnected) trigger.focus();
            resolve(confirmed);
        };
        const onCancel = (event) => { event.preventDefault(); finish(false); };
        const onAccept = () => finish(true);
        const onClose = () => { if (!dialog.open) finish(false); };
        const onLeave = () => finish(false, false);
        cancel.addEventListener('click', onCancel);
        accept.addEventListener('click', onAccept);
        dialog.addEventListener('cancel', onCancel);
        dialog.addEventListener('close', onClose);
        document.defaultView.addEventListener('pagehide', onLeave);
        activeDialogs.set(document, dialog);
        dialog.showModal();
        cancel.focus();
    });
}

export function initConfirmations(document) {
    const approved = new WeakSet();
    let pending = new WeakSet();
    let submitted = new WeakSet();
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('form[data-confirm]') || event.defaultPrevented) return;
        if (submitted.has(form)) { event.preventDefault(); return; }
        if (approved.has(form)) {
            submitted.add(form);
            form.setAttribute('aria-busy', 'true');
            return;
        }
        event.preventDefault();
        if (pending.has(form)) return;
        pending.add(form);
        const submitter = event.submitter;
        const label = submitter?.dataset.confirmLabel || submitter?.textContent.trim() || 'Confirmar';
        const confirmed = await confirmAction(document, {
            title: submitter?.dataset.confirmTitle || form.dataset.confirmTitle || label,
            message: submitter?.dataset.confirm || form.dataset.confirm,
            detail: form.dataset.confirmDetail,
            label,
        }, submitter || document.activeElement);
        pending.delete(form);
        if (!confirmed || !form.isConnected || (submitter && (submitter.disabled || submitter.form !== form))) return;
        approved.add(form);
        try {
            // requestSubmit validates again and preserves the clicked button's name/value.
            if (submitter) form.requestSubmit(submitter);
            else form.requestSubmit();
        } finally { approved.delete(form); }
    });
    document.defaultView.addEventListener('pageshow', () => {
        pending = new WeakSet();
        submitted = new WeakSet();
        document.querySelectorAll('form[data-confirm][aria-busy]').forEach((form) => form.removeAttribute('aria-busy'));
    });
}
