{* Bootstrap 5 Checkbox *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    <input
        type="checkbox"
        {$attributes|attributes nofilter}
        class="{$classes.input}"
        {if $value}checked{/if}
    />
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
