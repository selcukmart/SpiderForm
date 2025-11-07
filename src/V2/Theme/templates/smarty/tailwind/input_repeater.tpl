{* Tailwind CSS Repeater Template *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="{$classes.label}">
            {$label}
            {if $required}<span class="text-red-500 ml-1">*</span>{/if}
        </label>
    {/if}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}

    <div data-repeater="{$attributes.id}">
        {* Template row (hidden) *}
        <div class="{$classes.item}" data-repeater-template style="display: none;">
            <div class="{$classes['item-header']}">
                <span class="font-semibold text-gray-700" data-repeater-row-number></span>
                <button type="button" class="{$classes['remove-button']}" data-repeater-remove>
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="{$classes['item-body']}">
                {if $repeaterFields}
                    {foreach $repeaterFields.inputs as $field}
                        {if $field.is_section}
                            {foreach $field.inputs as $section_input}
                                {include file=$section_input.template}
                            {/foreach}
                        {else}
                            {include file=$field.template}
                        {/if}
                    {/foreach}
                {/if}
            </div>
        </div>

        {* Initial rows (if any default data) *}
        {if $value && is_array($value) && count($value) > 0}
            {foreach $value as $index => $rowData}
                <div class="{$classes.item}" data-repeater-item>
                    <div class="{$classes['item-header']}">
                        <span class="font-semibold text-gray-700" data-repeater-row-number>#{$index + 1}</span>
                        <button type="button" class="{$classes['remove-button']}" data-repeater-remove>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove
                        </button>
                    </div>
                    <div class="{$classes['item-body']}">
                        {if $repeaterFields}
                            {foreach $repeaterFields.inputs as $field}
                                {if $field.is_section}
                                    {foreach $field.inputs as $section_input}
                                        {include file=$section_input.template}
                                    {/foreach}
                                {else}
                                    {include file=$field.template}
                                {/if}
                            {/foreach}
                        {/if}
                    </div>
                </div>
            {/foreach}
        {/if}

        {* Add button container *}
        <div class="mt-3" data-repeater-add-container>
            <button type="button" class="{$classes['add-button']}" data-repeater-add>
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Item
            </button>
        </div>
    </div>
</div>
