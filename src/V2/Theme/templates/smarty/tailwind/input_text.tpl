{* Tailwind CSS Text Input *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label && $type != 'hidden'}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-red-500">*</span>{/if}
        </label>
    {/if}

    <input
        type="{$type}"
        {$attributes|attributes nofilter}
        class="{$classes.input}{if $required} required{/if}"
        {if $value}value="{$value}"{/if}
    />

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
