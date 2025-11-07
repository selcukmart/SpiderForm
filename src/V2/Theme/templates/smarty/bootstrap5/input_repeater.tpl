{* Bootstrap 5 Repeater Template *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}

    <div data-repeater="{$attributes.id}">
        {* Template row (hidden) *}
        <div class="{$classes.item}" data-repeater-template style="display: none;">
            <div class="{$classes['item-header']}">
                <span class="fw-bold" data-repeater-row-number></span>
                <button type="button" class="{$classes['remove-button']}" data-repeater-remove>
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
            <div class="{$classes['item-body']}">
                {if $repeaterFields}
                    {foreach $repeaterFields.inputs as $field}
                        {if $field.is_section}
                            {foreach $field.inputs as $section_input}
                                {foreach $section_input as $key => $value}
                                    {if $key != 'template'}
                                        {assign var=$key value=$value}
                                    {/if}
                                {/foreach}
                                {include file=$section_input.template}
                            {/foreach}
                        {else}
                            {foreach $field as $key => $value}
                                {if $key != 'template'}
                                    {assign var=$key value=$value}
                                {/if}
                            {/foreach}
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
                        <span class="fw-bold" data-repeater-row-number>#{$index + 1}</span>
                        <button type="button" class="{$classes['remove-button']}" data-repeater-remove>
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="{$classes['item-body']}">
                        {if $repeaterFields}
                            {foreach $repeaterFields.inputs as $field}
                                {if $field.is_section}
                                    {foreach $field.inputs as $section_input}
                                        {foreach $section_input as $key => $value}
                                            {if $key != 'template'}
                                                {assign var=$key value=$value}
                                            {/if}
                                        {/foreach}
                                        {include file=$section_input.template}
                                    {/foreach}
                                {else}
                                    {foreach $field as $key => $value}
                                        {if $key != 'template'}
                                            {assign var=$key value=$value}
                                        {/if}
                                    {/foreach}
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
                <i class="bi bi-plus-circle"></i> Add Item
            </button>
        </div>
    </div>
</div>
