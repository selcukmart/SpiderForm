{{-- Bootstrap 3 Button --}}
<button
    type="{{ $type ?? 'button' }}"
    {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
    class="{{ $classes['button'] ?? '' }}"
>
    {{ $label ?? 'Button' }}
</button>
