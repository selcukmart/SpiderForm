{{-- Tailwind CSS Range Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <input
        type="range"
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}"
        @if($value ?? false)value="{{ $value }}"@endif
    />

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
