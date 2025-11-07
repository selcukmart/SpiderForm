{{-- Bootstrap 3 Checkbox Input --}}
<div class="{{ $classes['wrapper'] ?? '' }}@if($error ?? false) has-error@endif" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    <label>
        <input
            type="checkbox"
            {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
            class="{{ $classes['input'] ?? '' }}"
            @if($checked ?? $value ?? false) checked @endif
        />
        @if($label ?? '')
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        @endif
    </label>

    @if($helpText ?? '')
        <span class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</span>
    @endif

    @if($error ?? '')
        <span class="{{ $classes['error'] ?? '' }}">{{ $error }}</span>
    @endif
</div>
