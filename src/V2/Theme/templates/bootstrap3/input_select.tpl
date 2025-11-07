{* Bootstrap 3 Select Dropdown *}
<div class="{$classes.wrapper}{if $error} has-error{/if}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label for="{$attributes.id}" class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
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
        <span class="{$classes.help}">{$helpText nofilter}</span>
    {/if}

    {if $error}
        <span class="{$classes.error}">{$error}</span>
    {/if}
</div>
