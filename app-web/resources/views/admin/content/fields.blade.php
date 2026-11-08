@foreach($fields as $field => $definition)
    @php($inputId = 'content-'.$group.'-'.$field)
    <label for="{{ $inputId }}">{{ $definition['label'] }}
        @if($definition['type'] === 'textarea')
            <textarea id="{{ $inputId }}" name="payload[{{ $group }}][{{ $field }}]" rows="{{ $field === 'privacy_content' ? 14 : 4 }}" maxlength="{{ $definition['max'] }}" @required(!($definition['optional'] ?? false) && !($definition['draft_optional'] ?? false))>{{ old('payload.'.$group.'.'.$field, $payload[$group][$field] ?? '') }}</textarea>
        @else
            <input id="{{ $inputId }}" type="{{ $definition['type'] }}" name="payload[{{ $group }}][{{ $field }}]" value="{{ old('payload.'.$group.'.'.$field, $payload[$group][$field] ?? '') }}" maxlength="{{ $definition['max'] }}" @required(!($definition['optional'] ?? false))>
        @endif
    </label>
    @error($group.'.'.$field)<p class="field-error">{{ $message }}</p>@enderror
@endforeach
