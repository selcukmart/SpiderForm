{{-- Bootstrap 5 File Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label for="{{ $attributes['id'] ?? '' }}" class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <input
        type="file"
        {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
        class="{{ $classes['input'] ?? '' }}"
    />

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
