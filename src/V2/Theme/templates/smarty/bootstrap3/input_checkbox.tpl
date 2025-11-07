{* Bootstrap 3 Checkbox *}
<div class="{$classes.wrapper}{if $error} has-error{/if}" {$wrapperAttributes|attributes nofilter}>
    <label>
        <input
            type="checkbox"
            {$attributes|attributes nofilter}
            class="{$classes.input}"
            {if $value}checked{/if}
        />
        {if $label}
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        {/if}
    </label>

    {if $helpText}
        <span class="{$classes.help}">{$helpText nofilter}</span>
    {/if}

    {if $error}
        <span class="{$classes.error}">{$error}</span>
    {/if}
</div>
