{* Bootstrap 5 File Input *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    <input
        type="file"
        {$attributes|attributes nofilter}
        class="{$classes.input}"
    />

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
