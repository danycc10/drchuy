export function initDevicePreview(document) {
    const panel = document.querySelector('[data-device-preview]');
    if (!panel) return;
    const frame = panel.querySelector('[data-preview-frame]');
    const buttons = [...panel.querySelectorAll('[data-preview-width]')];
    const status = panel.querySelector('[data-preview-status]');
    panel.querySelector('[data-device-buttons]').hidden = false;
    buttons.forEach((button) => button.addEventListener('click', () => {
        frame.width = button.dataset.previewWidth;
        buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        status.textContent = `Vista de ${button.dataset.previewWidth} px. Desplázate dentro de la vista para revisar la página.`;
    }));
    panel.querySelector('[data-preview-refresh]').addEventListener('click', () => {
        frame.src = frame.getAttribute('src');
        status.textContent = 'Cargando los últimos borradores guardados…';
    });
    frame.addEventListener('load', () => { status.textContent = `Vista cargada a ${frame.width} px.`; });
}
