{* Bootstrap 5 Range Input *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    <input
        type="range"
        {$attributes|attributes nofilter}
        class="{$classes.input}"
        {if $value}value="{$value}"{/if}
    />

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
