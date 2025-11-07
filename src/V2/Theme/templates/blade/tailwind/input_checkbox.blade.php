{{-- Tailwind CSS Checkbox --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    <input
        type="checkbox"
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}"
        @if($value ?? false)checked@endif
    />
    @if($label ?? false)
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
