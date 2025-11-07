{{-- Bootstrap 5 Select Dropdown --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <select
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}@if($required ?? false) required@endif"
    >
        @if($placeholder ?? false)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options ?? [] as $key => $option_label)
            <option value="{{ $key }}" @if(($value ?? '') == $key)selected@endif>
                {{ $option_label }}
            </option>
        @endforeach
    </select>

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
