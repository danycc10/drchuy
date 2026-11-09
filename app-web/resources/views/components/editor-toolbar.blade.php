@props(['form'])
<div class="editor-toolbar" aria-label="Acciones de edición">
    <div>
        <p class="save-state" data-save-state role="status" aria-live="polite">Versión guardada</p>
        <p class="field-help" id="save-help">Guardar conserva un borrador. Publicar actualiza el sitio.</p>
    </div>
    <button class="button primary" type="submit" form="{{ $form }}" data-save-button aria-describedby="save-help"><x-site-icon name="save" /><span data-save-label>Guardar borrador</span></button>
</div>
