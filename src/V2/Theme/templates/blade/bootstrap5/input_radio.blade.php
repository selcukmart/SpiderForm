{{-- Bootstrap 5 Radio Buttons --}}
<div class="mb-3" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label class="form-label">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @foreach($options ?? [] as $key => $option_label)
        <div class="{{ $classes['wrapper'] ?? '' }}">
            <input
                type="radio"
                name="{{ $attributes['name'] ?? '' }}"
                id="{{ ($attributes['id'] ?? '') . '_' . $key }}"
                value="{{ $key }}"
                class="{{ $classes['input'] ?? '' }}"
                {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($attributes ?? []) !!}
                @if(($value ?? '') == $key)checked@endif
            />
            <label for="{{ ($attributes['id'] ?? '') . '_' . $key }}" class="{{ $classes['label'] ?? '' }}">
                {{ $option_label }}
            </label>
        </div>
    @endforeach

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif
</div>
