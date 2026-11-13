<article class="visit-card" data-visit-card aria-labelledby="visit-card-title">
    <div class="visit-card-heading"><span class="visit-card-icon"><x-site-icon name="building" /></span><div><p class="eyebrow" id="visit-card-title">{{ $locale === 'es' ? 'Para tu visita' : 'Plan your visit' }}</p><p class="visit-physician">{{ $clinic['physician'] }}</p></div></div>
    @if($preview)<p class="visit-preview-note">Vista previa privada · última versión guardada</p>@endif
    <div class="visit-card-details">
        <div>
            <h3><x-site-icon name="pin" /> {{ $copy['address_label'] }}</h3>
            <address data-visit-address tabindex="-1">{{ $clinic['address'] }}<br>{{ $clinic['city'] }}</address>
            <div class="visit-address-actions">
                <a class="button secondary" href="{{ $clinic['maps'] }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="pin" /> {{ $copy['directions'] }}<x-new-tab-hint :copy="$copy" /></a>
                <button class="visit-text-button" type="button" data-copy-address hidden data-success="{{ $locale === 'es' ? 'Dirección copiada. Puedes pegarla en otra aplicación.' : 'Address copied. You can paste it into another app.' }}" data-fallback="{{ $locale === 'es' ? 'No se pudo copiar automáticamente. La dirección está seleccionada; cópiala con el menú de tu dispositivo.' : 'Automatic copying was unavailable. The address is selected; copy it using your device menu.' }}"><x-site-icon name="copy" /> {{ $locale === 'es' ? 'Copiar dirección' : 'Copy address' }}</button>
            </div>
            <p class="visit-feedback" data-copy-status role="status" aria-live="polite" aria-atomic="true"></p>
            <a class="visit-phone" href="tel:{{ $clinic['phone'] }}"><x-site-icon name="phone" /> {{ $clinic['phone_display'] }}</a>
            <p class="payment">{{ $copy['payment'] }}</p>
        </div>
        <div id="hours" tabindex="-1"><h3><x-site-icon name="clock" /> {{ $copy['hours_label'] }}</h3>@include('public.hours')</div>
    </div>
    <div class="visit-card-footer">
        <p>{{ $locale === 'es' ? 'Confirma la fecha y hora con el consultorio antes de acudir.' : 'Confirm the date and time with the office before your visit.' }}</p>
        @unless($preview)<button class="visit-text-button" type="button" data-print-visit hidden data-error="{{ $locale === 'es' ? 'No se pudo abrir la impresión. Usa la opción de imprimir de tu navegador.' : 'Printing could not be opened. Use your browser’s print option.' }}"><x-site-icon name="print" /> {{ $locale === 'es' ? 'Imprimir ficha' : 'Print details' }}</button>@endunless
    </div>
    <p class="visit-print-note">{{ $locale === 'es' ? 'Información consultada el ' : 'Information viewed on ' }}{{ now('America/Matamoros')->locale($locale)->isoFormat('D MMM YYYY') }}. {{ $locale === 'es' ? 'Esta ficha no confirma una cita. Los horarios pueden cambiar.' : 'These details do not confirm an appointment. Office hours may change.' }}</p>
</article>
