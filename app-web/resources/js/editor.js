export function initEditor(document) {
    const window = document.defaultView;
    const editor = document.querySelector('[data-editor]');
    const tabs = [...document.querySelectorAll('[data-language-tabs] [role=tab]')];
    const panels = [...document.querySelectorAll('[data-language-panel]')];
    document.querySelector('a[href="#editor-workflow"]')?.addEventListener('click', () => {
        const workflow = document.getElementById('editor-workflow');
        if (workflow) { workflow.open = true; workflow.querySelector('summary').focus(); }
    });

    function selectTab(tab, focus = false) {
        tabs.forEach((item) => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
        });
        panels.forEach((panel) => { panel.hidden = panel.id !== tab.getAttribute('aria-controls'); });
        if (focus) tab.focus();
    }

    function reveal(field) {
        let disclosure = field.closest('details');
        while (disclosure) { disclosure.open = true; disclosure = disclosure.parentElement.closest('details'); }
        const panel = field.closest('[data-language-panel]');
        if (panel) selectTab(tabs.find((tab) => tab.getAttribute('aria-controls') === panel.id));
        const times = field.closest('[data-hours-times]');
        if (times) times.hidden = false;
    }

    if (tabs.length) {
        document.querySelector('[data-language-tabs]').hidden = false;
        panels.forEach((panel) => {
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('aria-labelledby', tabs.find((tab) => tab.getAttribute('aria-controls') === panel.id).id);
        });
        selectTab(tabs[0]);
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => selectTab(tab));
            tab.addEventListener('keydown', (event) => {
                const next = { ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index + tabs.length - 1) % tabs.length, Home: 0, End: tabs.length - 1 }[event.key];
                if (next !== undefined) { event.preventDefault(); selectTab(tabs[next], true); }
            });
        });
    }

    document.querySelectorAll('[data-error-field]').forEach((message, index) => {
        const parts = message.dataset.errorField.split('.');
        const name = parts[0] + parts.slice(1).map((part) => `[${part}]`).join('');
        const names = [name, `payload[${parts[0]}]` + parts.slice(1).map((part) => `[${part}]`).join('')];
        const errorScope = document.querySelector('[data-error-target="true"]') ?? document;
        const fields = [...errorScope.querySelectorAll('input:not([type=hidden]), textarea, select')];
        const field = fields.find((input) => names.includes(input.name))
            ?? fields.find((input) => names.some((prefix) => input.name.startsWith(`${prefix}[`)));
        if (!field) return;
        field.id ||= `error-input-${index}`;
        message.id = `error-message-${index}`;
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), message.id].filter(Boolean).join(' '));
        const link = document.createElement('a');
        link.href = `#${field.id}`;
        link.textContent = message.textContent;
        message.replaceChildren(link);
        link.addEventListener('click', (event) => { event.preventDefault(); reveal(field); field.focus(); });
    });
    document.querySelector('[data-error-summary]')?.focus();

    // Show the first invalid field's language before native validation focuses it.
    let firstInvalid = null;
    editor?.addEventListener('invalid', (event) => {
        if (!firstInvalid) {
            firstInvalid = event.target;
            reveal(firstInvalid);
            window.queueMicrotask(() => { firstInvalid = null; });
        } else {
            event.preventDefault();
        }
    }, true);

    editor?.querySelectorAll('textarea[maxlength]').forEach((field, index) => {
        const counter = document.createElement('p');
        counter.className = 'field-help character-count';
        counter.id = `character-count-${index}`;
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), counter.id].filter(Boolean).join(' '));
        field.closest('label').after(counter);
        const update = () => { counter.textContent = `${field.value.length.toLocaleString('es-MX')} / ${field.maxLength.toLocaleString('es-MX')} caracteres`; };
        field.addEventListener('input', update);
        update();
    });

    const snapshot = () => JSON.stringify([...new window.FormData(editor).entries()]);
    const initial = editor ? snapshot() : null;
    const recovered = editor?.dataset.recoveredInput === 'true';
    const saveButton = document.querySelector('[data-save-button]');
    const saveLabel = saveButton?.querySelector('[data-save-label]') ?? saveButton;
    const publishButtons = [...document.querySelectorAll('[data-publish-button]')].map((button) => ({ button, disabled: button.disabled }));
    let dirty = false;
    let submitting = false;

    function updateState() {
        if (!editor || submitting) return;
        dirty = recovered || snapshot() !== initial;
        if (saveButton) saveButton.disabled = !dirty;
        const status = document.querySelector('[data-save-state]');
        if (status) {
            status.textContent = dirty ? 'Cambios sin guardar' : 'Todos los cambios guardados';
            status.classList.toggle('unsaved', dirty);
        }
        document.querySelectorAll('[data-dirty-note]').forEach((note) => { note.hidden = !dirty; });
        publishButtons.forEach(({ button, disabled }) => { button.disabled = disabled || dirty; });
    }
    editor?.addEventListener('input', updateState);
    editor?.addEventListener('change', updateState);
    editor?.addEventListener('submit', (event) => {
        if (submitting) { event.preventDefault(); return; }
        submitting = true;
        dirty = false;
        editor.setAttribute('aria-busy', 'true');
        if (saveButton) { saveButton.disabled = true; saveLabel.textContent = 'Guardando…'; }
    });
    window.addEventListener('beforeunload', (event) => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });
    window.addEventListener('pageshow', () => {
        submitting = false;
        editor?.removeAttribute('aria-busy');
        if (saveLabel) saveLabel.textContent = 'Guardar borrador';
        updateState();
    });
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (dirty) {
                event.preventDefault();
                saveButton?.focus();
            } else if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
    updateState();
}
