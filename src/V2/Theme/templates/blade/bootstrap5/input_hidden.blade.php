{{-- Bootstrap 5 Hidden Input --}}
<input
    type="hidden"
    {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
    @if($value ?? false)value="{{ $value }}"@endif
/>
