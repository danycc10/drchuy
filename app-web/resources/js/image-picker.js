export function initImagePicker(document) {
    const section = document.querySelector('[data-service-image]');
    const picker = section?.querySelector('[data-image-picker]');
    if (!picker) return;
    const select = section.querySelector('[data-media-select]');
    const query = picker.querySelector('[data-image-search]');
    const grid = picker.querySelector('[data-image-grid]');
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const buttons = new Map();
    function filter() {
        let visible = 0;
        const terms = normalize(query.value).split(/\s+/).filter(Boolean);
        buttons.forEach((button) => {
            button.hidden = !terms.every((term) => normalize(button.dataset.title).includes(term));
            if (!button.hidden) visible++;
        });
        picker.querySelector('[data-image-empty]').hidden = visible > 0;
        picker.querySelector('[data-image-count]').textContent = `${visible} de ${buttons.size} opciones`;
    }
    function sync() {
        for (const option of select.options) {
            if (!buttons.has(option.value)) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'image-picker-option';
                button.dataset.title = option.textContent;
                button.dataset.imageValue = option.value;
                const image = document.createElement('img');
                image.src = option.dataset.src;
                image.alt = ''; image.loading = 'lazy'; image.width = 180; image.height = 120;
                const title = document.createElement('span'); title.textContent = option.textContent;
                const check = document.createElement('span'); check.className = 'image-picker-check'; check.textContent = 'Seleccionada'; check.setAttribute('aria-hidden', 'true');
                button.append(image, title, check);
                button.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new document.defaultView.Event('change', { bubbles: true }));
                });
                buttons.set(option.value, button); grid.append(button);
            }
        }
        buttons.forEach((button, value) => button.setAttribute('aria-pressed', String(select.value === value)));
        picker.querySelector('[data-image-selection]').textContent = `Seleccionada: ${select.selectedOptions[0]?.textContent ?? 'Ninguna'}`;
        filter();
    }
    query.addEventListener('input', filter);
    query.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && query.value) { event.preventDefault(); query.value = ''; filter(); }
        if (event.key === 'Enter') event.preventDefault();
    });
    select.addEventListener('change', sync);
    section.addEventListener('image:uploaded', () => { query.value = ''; sync(); });
    document.defaultView.addEventListener('pageshow', sync);
    sync(); picker.hidden = false;
    section.querySelector('[data-image-native]').open = false;
}
