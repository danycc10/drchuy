@if(session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())
    <div class="notice error" role="alert" data-error-summary tabindex="-1">
        <strong>Revisa los siguientes datos:</strong>
        <ul>@foreach($errors->messages() as $field => $messages)@foreach($messages as $error)<li><span data-error-field="{{ $field }}">{{ $error }}</span></li>@endforeach @endforeach</ul>
    </div>
@endif
