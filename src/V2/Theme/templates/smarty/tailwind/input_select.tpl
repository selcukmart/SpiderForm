{* Tailwind CSS Select Dropdown *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-red-500">*</span>{/if}
        </label>
    {/if}

    <select
        {$attributes|attributes nofilter}
        class="{$classes.input}{if $required} required{/if}"
    >
        {if $placeholder}
            <option value="">{$placeholder}</option>
        {/if}
        {foreach $options as $key => $option_label}
            <option value="{$key}" {if $value == $key}selected{/if}>
                {$option_label}
            </option>
        {/foreach}
    </select>

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
