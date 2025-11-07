{* Bootstrap 3 Input Wrapper/Capsule *}
<div class="{$input.classes.wrapper}">
    {if $input.label && !in_array($input.type, ['checkbox', 'radio', 'hidden'])}
        <label for="{$input.attributes.id}" class="{$input.classes.label}">
            {$input.label}
            {if $input.required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    {$input_content nofilter}

    {if $input.helpText}
        <div class="{$input.classes.help}">{$input.helpText}</div>
    {/if}

    {if $input.error}
        <div class="{$input.classes.error}">{$input.error}</div>
    {/if}
</div>
