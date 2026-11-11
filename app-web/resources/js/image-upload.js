export function uploadImage(window, url, body, onProgress, signal) {
    if (!window.XMLHttpRequest) {
        return window.fetch(url, { method: 'POST', body, headers: { Accept: 'application/json' }, credentials: 'same-origin', signal });
    }
    return new Promise((resolve, reject) => {
        const xhr = new window.XMLHttpRequest();
        let settled = false;
        const abort = () => { xhr.abort(); finish(null, new window.DOMException('Carga interrumpida', 'AbortError')); };
        const finish = (response, error) => {
            if (settled) return;
            settled = true;
            signal.removeEventListener('abort', abort);
            error ? reject(error) : resolve(response);
        };
        xhr.open('POST', url);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.timeout = 120000;
        xhr.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable && !settled) onProgress(Math.min(100, Math.round(event.loaded / event.total * 100)));
        });
        xhr.addEventListener('load', () => {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch { /* Non-JSON errors use the generic message. */ }
            finish({ ok: xhr.status >= 200 && xhr.status < 300, status: xhr.status, json: async () => data });
        });
        xhr.addEventListener('error', () => finish(null, new Error('Se perdió la conexión. Revisa la biblioteca antes de reintentar; la imagen pudo haberse guardado.')));
        xhr.addEventListener('timeout', () => finish(null, new Error('No llegó la confirmación a tiempo. Revisa la biblioteca antes de reintentar para evitar duplicados.')));
        xhr.addEventListener('abort', () => finish(null, new window.DOMException('Carga interrumpida', 'AbortError')));
        signal.addEventListener('abort', abort, { once: true });
        if (signal.aborted) abort();
        else xhr.send(body);
    });
}
