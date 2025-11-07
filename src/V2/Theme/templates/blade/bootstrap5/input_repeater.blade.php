{{-- Bootstrap 5 Repeater Template --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif

    <div data-repeater="{{ $attributes['id'] ?? '' }}">
        {{-- Template row (hidden) --}}
        <div class="{{ $classes['item'] ?? '' }}" data-repeater-template style="display: none;">
            <div class="{{ $classes['item-header'] ?? '' }}">
                <span class="fw-bold" data-repeater-row-number></span>
                <button type="button" class="{{ $classes['remove-button'] ?? '' }}" data-repeater-remove>
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="{{ $classes['item-body'] ?? '' }}">
                @if($repeaterFields ?? false)
                    @foreach($repeaterFields['inputs'] ?? [] as $field)
                        @if($field['is_section'] ?? false)
                            @foreach($field['inputs'] ?? [] as $section_input)
                                @include($section_input['template'] ?? '', $section_input)
                            @endforeach
                        @else
                            @include($field['template'] ?? '', $field)
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Initial rows (if any default data) --}}
        @if(is_iterable($value ?? null) && !empty($value))
            @foreach($value as $index => $rowData)
                <div class="{{ $classes['item'] ?? '' }}" data-repeater-item>
                    <div class="{{ $classes['item-header'] ?? '' }}">
                        <span class="fw-bold" data-repeater-row-number>#{{ $index + 1 }}</span>
                        <button type="button" class="{{ $classes['remove-button'] ?? '' }}" data-repeater-remove>
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="{{ $classes['item-body'] ?? '' }}">
                        @if($repeaterFields ?? false)
                            @foreach($repeaterFields['inputs'] ?? [] as $field)
                                @php
                                    $fieldWithValue = array_merge($field, ['value' => $rowData[$field['name']] ?? '']);
                                @endphp
                                @if($field['is_section'] ?? false)
                                    @foreach($field['inputs'] ?? [] as $section_input)
                                        @php
                                            $sectionFieldWithValue = array_merge($section_input, ['value' => $rowData[$section_input['name']] ?? '']);
                                        @endphp
                                        @include($section_input['template'] ?? '', $sectionFieldWithValue)
                                    @endforeach
                                @else
                                    @include($field['template'] ?? '', $fieldWithValue)
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Add button container --}}
        <div class="mt-3" data-repeater-add-container>
            <button type="button" class="{{ $classes['add-button'] ?? '' }}" data-repeater-add>
                <i class="bi bi-plus-circle"></i> Add Item
            </button>
        </div>
    </div>
</div>
