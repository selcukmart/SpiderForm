{* Bootstrap 5 Radio Buttons *}
<div class="mb-3" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="form-label">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    {foreach $options as $key => $option_label}
        <div class="{$classes.wrapper}">
            <input
                type="radio"
                name="{$attributes.name}"
                id="{$attributes.id}_{$key}"
                value="{$key}"
                class="{$classes.input}"
                {$attributes|attributes nofilter}
                {if $value == $key}checked{/if}
            />
            <label for="{$attributes.id}_{$key}" class="{$classes.label}">
                {$option_label}
            </label>
        </div>
    {/foreach}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}
</div>
