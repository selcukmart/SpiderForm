{{-- Tailwind CSS Button --}}
<button
    type="{{ $type }}"
    {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
    class="{{ $classes['button'] ?? '' }}"
>
    {{ $label }}
</button>
