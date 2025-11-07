{* Bootstrap 5 Text Input *}
<div class="{$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label && $type != 'hidden'}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    <input
            type="{$type}"
            {$attributes|attributes nofilter}
            class="{$classes.input}{if $required} required{/if}"
            {if $value and !is_array($value) }value="{$value}"
            {/if}
    />
{*    <pre> {$attributes|var_dump}</pre>*}
{*    <pre> {$value|var_dump}</pre>*}
    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
