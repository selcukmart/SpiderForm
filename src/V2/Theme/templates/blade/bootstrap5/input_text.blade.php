{{-- Bootstrap 5 Text Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if(($label ?? false) && !in_array($type ?? '', ['hidden']))
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}@if($required ?? false) required@endif"
        @if($value ?? false)value="{{ $value }}"@endif
    />

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
