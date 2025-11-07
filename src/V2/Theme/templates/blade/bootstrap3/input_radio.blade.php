{{-- Bootstrap 3 Radio Input --}}
<div class="form-group@if($error ?? false) has-error@endif" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? '')
        <label class="{{ $classes['label'] ?? 'control-label' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @foreach($options ?? [] as $optionValue => $optionLabel)
        <div class="{{ $classes['wrapper'] ?? 'radio' }}">
            <label>
                <input
                    type="radio"
                    name="{{ $attributes['name'] ?? '' }}"
                    value="{{ $optionValue }}"
                    class="{{ $classes['input'] ?? '' }}"
                    @if(($value ?? '') == $optionValue) checked @endif
                />
                {{ $optionLabel }}
            </label>
        </div>
    @endforeach

    @if($helpText ?? '')
        <span class="{{ $classes['help'] ?? 'help-block' }}">{{ $helpText }}</span>
    @endif

    @if($error ?? '')
        <span class="{{ $classes['error'] ?? 'help-block' }}">{{ $error }}</span>
    @endif
</div>
