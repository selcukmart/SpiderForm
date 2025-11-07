{{-- Bootstrap 5 Input Wrapper/Capsule --}}
<div class="{{ $input['classes']['wrapper'] ?? '' }}">
    @if(($input['label'] ?? false) && !in_array($input['type'] ?? '', ['checkbox', 'radio', 'hidden']))
        <label for="{{ $input['attributes']['id'] ?? '' }}" class="{{ $input['classes']['label'] ?? '' }}">
            {{ $input['label'] }}
            @if($input['required'] ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    {!! $input_content ?? '' !!}

    @if($input['helpText'] ?? false)
        <div class="{{ $input['classes']['help'] ?? '' }}">{{ $input['helpText'] }}</div>
    @endif

    @if($input['error'] ?? false)
        <div class="{{ $input['classes']['error'] ?? '' }}">{{ $input['error'] }}</div>
    @endif
</div>
