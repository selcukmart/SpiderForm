{{-- Bootstrap 3 Select Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}@if($error ?? false) has-error@endif" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? '')
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <select
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}@if($required ?? false) required@endif"
    >
        @if($placeholder ?? '')
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options ?? [] as $optionValue => $optionLabel)
            @if(is_array($optionLabel))
                {{-- Optgroup --}}
                <optgroup label="{{ $optionValue }}">
                    @foreach($optionLabel as $subValue => $subLabel)
                        <option value="{{ $subValue }}" @if(($value ?? '') == $subValue) selected @endif>
                            {{ $subLabel }}
                        </option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optionValue }}" @if(($value ?? '') == $optionValue) selected @endif>
                    {{ $optionLabel }}
                </option>
            @endif
        @endforeach
    </select>

    @if($helpText ?? '')
        <span class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</span>
    @endif

    @if($error ?? '')
        <span class="{{ $classes['error'] ?? '' }}">{{ $error }}</span>
    @endif
</div>
