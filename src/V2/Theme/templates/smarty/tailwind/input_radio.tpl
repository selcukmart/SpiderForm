{* Tailwind CSS Radio Buttons *}
<div class="mb-4" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {$label}
            {if $required}<span class="text-red-500">*</span>{/if}
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
