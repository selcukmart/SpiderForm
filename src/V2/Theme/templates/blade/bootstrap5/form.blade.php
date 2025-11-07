{{-- Bootstrap 5 Form Template --}}
@if($stepper_enabled ?? false)
    @include('bootstrap5/form_stepper.blade.php')
@else
<form {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($form['attributes'] ?? []) !!}>
    @if($csrf_token ?? false)
        <input type="hidden" name="_csrf_token" value="{{ $csrf_token }}" />
    @endif

    @foreach($inputs ?? [] as $input)
        @if($input['is_section'] ?? false)
            {{-- Render section with its inputs --}}
            @if($input['section'] ?? false)
                <div class="form-section mb-4 {{ implode(' ', $input['section']['classes'] ?? []) }}" @foreach($input['section']['attributes'] ?? [] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
                    @if($input['section']['collapsible'] ?? false)
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">
                                    <button class="btn btn-link w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#section-{{ $loop->index }}" aria-expanded="{{ !($input['section']['collapsed'] ?? false) ? 'true' : 'false' }}">
                                        {{ $input['section']['title'] ?? '' }}
                                    </button>
                                </h4>
                            </div>
                            <div id="section-{{ $loop->index }}" class="collapse {{ !($input['section']['collapsed'] ?? false) ? 'show' : '' }}">
                                <div class="card-body">
                                    @if($input['section']['description'] ?? '')
                                        <p class="text-muted mb-3">{!! $input['section']['description'] !!}</p>
                                    @endif
                                    @if($input['section']['htmlContent'] ?? '')
                                        <div class="section-content mb-3">{!! $input['section']['htmlContent'] !!}</div>
                                    @endif
                                    @foreach($input['inputs'] ?? [] as $section_input)
                                        @include($section_input['template'] ?? '')
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <h3 class="form-section-title mb-2">{{ $input['section']['title'] ?? '' }}</h3>
                        @if($input['section']['description'] ?? '')
                            <p class="text-muted mb-3">{!! $input['section']['description'] !!}</p>
                        @endif
                        @if($input['section']['htmlContent'] ?? '')
                            <div class="section-content mb-3">{!! $input['section']['htmlContent'] !!}</div>
                        @endif
                        @foreach($input['inputs'] ?? [] as $section_input)
                            @include($section_input['template'] ?? '')
                        @endforeach
                    @endif
                </div>
            @else
                {{-- Section is null - render inputs without section wrapper --}}
                @foreach($input['inputs'] ?? [] as $section_input)
                    @include($section_input['template'] ?? '')
                @endforeach
            @endif
        @else
            {{-- Regular input (no sections used) --}}
            @include($input['template'] ?? '')
        @endif
    @endforeach
</form>
@endif
