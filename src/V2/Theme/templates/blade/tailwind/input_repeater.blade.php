{{-- Tailwind CSS Repeater Template --}}
<div class="{{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-red-500 ml-1">*</span>@endif
        </label>
    @endif

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif

    <div data-repeater="{{ $attributes['id'] ?? '' }}">
        {{-- Template row (hidden) --}}
        <div class="{{ $classes['item'] ?? '' }}" data-repeater-template style="display: none;">
            <div class="{{ $classes['item-header'] ?? '' }}">
                <span class="font-semibold text-gray-700" data-repeater-row-number></span>
                <button type="button" class="{{ $classes['remove-button'] ?? '' }}" data-repeater-remove>
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
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
                        <span class="font-semibold text-gray-700" data-repeater-row-number>#{{ $index + 1 }}</span>
                        <button type="button" class="{{ $classes['remove-button'] ?? '' }}" data-repeater-remove>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove
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
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Item
            </button>
        </div>
    </div>
</div>
