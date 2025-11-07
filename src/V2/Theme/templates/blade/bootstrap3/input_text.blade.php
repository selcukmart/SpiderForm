{{-- Bootstrap 3 Text Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}@if($error ?? false) has-error@endif" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if(($label ?? '') && !in_array($type ?? 'text', ['hidden']))
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type ?? 'text' }}"
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}@if($required ?? false) required@endif"
        @if($value ?? false)value="{{ $value }}"@endif
    />

    @if($helpText ?? '')
        <span class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</span>
    @endif

    @if($error ?? '')
        <span class="{{ $classes['error'] ?? '' }}">{{ $error }}</span>
    @endif
</div>
