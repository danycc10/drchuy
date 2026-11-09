@foreach($fields as $field => $definition)
    @php($inputId = 'content-'.$group.'-'.$field)
    @if($definition['type'] === 'media')
        @include('admin.content.media-field')
    @else
    <label for="{{ $inputId }}">{{ $definition['label'] }}
        @if($definition['type'] === 'textarea')
            <textarea id="{{ $inputId }}" lang="{{ $group === 'en' ? 'en' : 'es' }}" name="payload[{{ $group }}][{{ $field }}]" rows="{{ $field === 'privacy_content' ? 14 : 4 }}" maxlength="{{ $definition['max'] }}" @required(!($definition['optional'] ?? false) && !($definition['draft_optional'] ?? false))>{{ old('payload.'.$group.'.'.$field, $payload[$group][$field] ?? '') }}</textarea>
        @else
            <input id="{{ $inputId }}" lang="{{ $group === 'en' ? 'en' : 'es' }}" type="{{ $definition['type'] }}" name="payload[{{ $group }}][{{ $field }}]" value="{{ old('payload.'.$group.'.'.$field, $payload[$group][$field] ?? '') }}" maxlength="{{ $definition['max'] }}" @required(!($definition['optional'] ?? false))>
        @endif
    </label>
    @endif
    @error($group.'.'.$field)<p class="field-error">{{ $message }}</p>@enderror
@endforeach
