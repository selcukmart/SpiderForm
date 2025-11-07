{* Bootstrap 3 Range Input *}
<div class="{$classes.wrapper}{if $error} has-error{/if}" {$wrapperAttributes|attributes nofilter}>
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
        <span class="{$classes.help}">{$helpText nofilter}</span>
    {/if}

    {if $error}
        <span class="{$classes.error}">{$error}</span>
    {/if}
</div>
