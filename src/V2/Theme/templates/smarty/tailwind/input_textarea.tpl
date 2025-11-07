{* Tailwind CSS Textarea *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-red-500">*</span>{/if}
        </label>
    {/if}

    <textarea
        {$attributes|attributes nofilter}
        class="{$classes.input}{if $required} required{/if}"
    >{$value}</textarea>

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
