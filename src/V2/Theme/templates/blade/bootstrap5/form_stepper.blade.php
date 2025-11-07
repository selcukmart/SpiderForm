{{-- Bootstrap 5 Form with Stepper Template --}}
<form {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($form['attributes'] ?? []) !!}>
    @if($csrf_token ?? false)
        <input type="hidden" name="_csrf_token" value="{{ $csrf_token }}" />
    @endif

    {{-- Stepper Container --}}
    <div class="stepper stepper-{{ $stepper_options['layout'] ?? 'horizontal' }}" data-stepper="{{ $form['name'] ?? '' }}">
        {{-- Stepper Navigation --}}
        <div class="stepper-nav @if(($stepper_options['layout'] ?? 'horizontal') == 'vertical')flex-column @else d-flex justify-content-between @endif mb-4">
            @php $step_index = 0; @endphp
            @foreach($inputs ?? [] as $input)
                @if(($input['is_section'] ?? false) && ($input['section'] ?? false))
                    <div class="stepper-nav-item @if($step_index == ($stepper_options['startIndex'] ?? 0))active @endif" data-stepper-nav="{{ $step_index }}">
                        <div class="stepper-nav-step">
                            <div class="stepper-nav-step-number">{{ $step_index + 1 }}</div>
                            <div class="stepper-nav-step-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stepper-nav-label">
                            <div class="stepper-nav-title">{{ $input['section']['title'] ?? '' }}</div>
                            @if($input['section']['description'] ?? false)
                                <div class="stepper-nav-desc text-muted small">{!! $input['section']['description'] !!}</div>
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)
                        <div class="stepper-nav-line"></div>
                    @endif
                    @php $step_index++; @endphp
                @endif
            @endforeach
        </div>

        {{-- Stepper Steps Content --}}
        <div class="stepper-content">
            @php $step_index = 0; @endphp
            @foreach($inputs ?? [] as $input)
                @if($input['is_section'] ?? false)
                    {{-- Render section as a step --}}
                    @if($input['section'] ?? false)
                        <div class="stepper-step @if($step_index == ($stepper_options['startIndex'] ?? 0))active @endif" data-stepper-step="{{ $step_index }}" style="display: @if($step_index == ($stepper_options['startIndex'] ?? 0))block @else none @endif;">
                            <div class="mb-4">
                                <h3 class="fw-bold mb-2">{{ $input['section']['title'] ?? '' }}</h3>
                                @if($input['section']['description'] ?? false)
                                    <p class="text-muted">{!! $input['section']['description'] !!}</p>
                                @endif
                                @if($input['section']['htmlContent'] ?? false)
                                    <div class="section-content mb-3">{!! $input['section']['htmlContent'] !!}</div>
                                @endif
                            </div>
                            <div class="stepper-step-fields">
                                @foreach($input['inputs'] ?? [] as $section_input)
                                    @include($section_input['template'] ?? '')
                                @endforeach
                            </div>
                        </div>
                        @php $step_index++; @endphp
                    @else
                        {{-- Section is null - render inputs without section wrapper --}}
                        <div class="stepper-step" data-stepper-step="0">
                            @foreach($input['inputs'] ?? [] as $section_input)
                                @include($section_input['template'] ?? '')
                            @endforeach
                        </div>
                    @endif
                @else
                    {{-- Regular input (no sections used) - wrap in default step --}}
                    @if($loop->first)
                        <div class="stepper-step active" data-stepper-step="0">
                    @endif
                    @include($input['template'] ?? '')
                    @if($loop->last)
                        </div>
                    @endif
                @endif
            @endforeach
        </div>

        {{-- Stepper Navigation Buttons --}}
        @if($stepper_options['showNavigationButtons'] ?? true)
            <div class="stepper-actions d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" data-stepper-action="prev">
                    <i class="bi bi-arrow-left"></i> Previous
                </button>
                <div>
                    <button type="button" class="btn btn-primary" data-stepper-action="next">
                        Next <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success" data-stepper-action="finish" style="display: none;">
                        <i class="bi bi-check-circle"></i> Finish
                    </button>
                </div>
            </div>
        @endif
    </div>
</form>

<style>
/* Stepper Horizontal Layout */
.stepper-nav {
    position: relative;
}

.stepper-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    text-align: center;
}

.stepper.stepper-horizontal .stepper-nav {
    display: flex;
}

.stepper.stepper-horizontal .stepper-nav-item {
    flex-direction: row;
    align-items: flex-start;
}

.stepper.stepper-horizontal .stepper-nav-label {
    margin-left: 0.75rem;
    text-align: left;
}

.stepper-nav-step {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    border-radius: 50%;
    transition: all 0.3s ease;
    z-index: 2;
}

.stepper-nav-step-number {
    font-weight: 600;
    color: #6c757d;
}

.stepper-nav-step-icon {
    display: none;
    color: #fff;
}

.stepper-nav-item.active .stepper-nav-step {
    background: #0d6efd;
}

.stepper-nav-item.active .stepper-nav-step-number {
    color: #fff;
}

.stepper-nav-item.completed .stepper-nav-step {
    background: #198754;
}

.stepper-nav-item.completed .stepper-nav-step-number {
    display: none;
}

.stepper-nav-item.completed .stepper-nav-step-icon {
    display: block;
}

.stepper-nav-item.error .stepper-nav-step {
    background: #dc3545;
}

.stepper-nav-title {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.stepper-nav-desc {
    font-size: 0.8rem;
}

.stepper-nav-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    align-self: center;
    margin: 0 0.5rem;
}

/* Vertical Layout */
.stepper.stepper-vertical .stepper-nav {
    flex-direction: column;
}

.stepper.stepper-vertical .stepper-nav-item {
    flex-direction: row;
    align-items: flex-start;
    width: 100%;
    text-align: left;
}

.stepper.stepper-vertical .stepper-nav-label {
    margin-left: 1rem;
}

.stepper.stepper-vertical .stepper-nav-line {
    width: 2px;
    height: 30px;
    margin: 0;
    margin-left: 19px;
}

/* Step Content */
.stepper-step {
    transition: opacity 0.3s ease;
}

.stepper-step.active {
    opacity: 1;
}

/* Clickable steps in non-linear mode */
[data-stepper-nav] {
    cursor: default;
}

.stepper[data-stepper].non-linear [data-stepper-nav] {
    cursor: pointer;
}

.stepper[data-stepper].non-linear [data-stepper-nav]:hover .stepper-nav-step {
    background: #0d6efd;
}

.stepper[data-stepper].non-linear [data-stepper-nav]:hover .stepper-nav-step-number {
    color: #fff;
}
</style>
