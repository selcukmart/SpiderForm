{{-- Tailwind CSS Form Template --}}
@if($stepper_enabled ?? false)
    @include('tailwind/form_stepper.blade.php')
@else
<form {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($form['attributes'] ?? []) !!}>
    @if($csrf_token ?? false)
        <input type="hidden" name="_csrf_token" value="{{ $csrf_token }}" />
    @endif

    @foreach($inputs ?? [] as $input)
        @if($input['is_section'] ?? false)
            {{-- Render section with its inputs --}}
            @if($input['section'] ?? false)
                <div class="form-section mb-6 {{ implode(' ', $input['section']['classes'] ?? []) }}" @foreach($input['section']['attributes'] ?? [] as $attr => $value) {{ $attr }}="{{ $value }}" @endforeach>
                    @if($input['section']['collapsible'] ?? false)
                        <details {{ ($input['section']['collapsed'] ?? false) ? '' : 'open' }} class="border border-gray-300 rounded-lg">
                            <summary class="bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 rounded-t-lg">
                                <h3 class="inline text-lg font-semibold text-gray-900">{{ $input['section']['title'] ?? '' }}</h3>
                            </summary>
                            <div class="p-4">
                                @if($input['section']['description'] ?? false)
                                    <p class="text-gray-600 mb-4">{!! $input['section']['description'] !!}</p>
                                @endif
                                @if($input['section']['htmlContent'] ?? false)
                                    <div class="section-content mb-4">{!! $input['section']['htmlContent'] !!}</div>
                                @endif
                                @foreach($input['inputs'] ?? [] as $section_input)
                                    @include($section_input['template'] ?? '')
                                @endforeach
                            </div>
                        </details>
                    @else
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $input['section']['title'] ?? '' }}</h3>
                        @if($input['section']['description'] ?? false)
                            <p class="text-gray-600 mb-4">{!! $input['section']['description'] !!}</p>
                        @endif
                        @if($input['section']['htmlContent'] ?? false)
                            <div class="section-content mb-4">{!! $input['section']['htmlContent'] !!}</div>
                        @endif
                        <div class="border-l-4 border-indigo-500 pl-4">
                            @foreach($input['inputs'] ?? [] as $section_input)
                                @include($section_input['template'] ?? '')
                            @endforeach
                        </div>
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
