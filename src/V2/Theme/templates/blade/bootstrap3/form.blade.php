{{-- Bootstrap 3 Form Template --}}
<form {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($form['attributes'] ?? []) !!}>
    @if($csrf_token ?? false)
        <input type="hidden" name="_csrf_token" value="{{ $csrf_token }}" />
    @endif

    @foreach($inputs ?? [] as $input)
        @if($input['is_section'] ?? false)
            {{-- Render section with its inputs --}}
            @if($input['section'] ?? false)
                <div class="form-section {{ implode(' ', $input['section']['classes'] ?? []) }}">
                    @if($input['section']['collapsible'] ?? false)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" href="#section-{{ $loop->index }}">
                                        {{ $input['section']['title'] ?? '' }}
                                    </a>
                                </h4>
                            </div>
                            <div id="section-{{ $loop->index }}" class="panel-collapse collapse {{ !($input['section']['collapsed'] ?? false) ? 'in' : '' }}">
                                <div class="panel-body">
                                    @if($input['section']['description'] ?? '')
                                        <p class="text-muted">{!! $input['section']['description'] !!}</p>
                                    @endif
                                    @if($input['section']['htmlContent'] ?? '')
                                        <div class="section-content">{!! $input['section']['htmlContent'] !!}</div>
                                    @endif
                                    @foreach($input['inputs'] ?? [] as $section_input)
                                        @include($section_input['template'] ?? '')
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <h3 class="form-section-title">{{ $input['section']['title'] ?? '' }}</h3>
                        @if($input['section']['description'] ?? '')
                            <p class="text-muted">{!! $input['section']['description'] !!}</p>
                        @endif
                        @if($input['section']['htmlContent'] ?? '')
                            <div class="section-content">{!! $input['section']['htmlContent'] !!}</div>
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
