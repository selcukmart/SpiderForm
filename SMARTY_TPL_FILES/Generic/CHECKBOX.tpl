{if isset($checked) && $checked == 'checked' }
    {$checked = 'checked="checked"'}
{else}
    {$checked = ''}
{/if}
<input type="checkbox" id="{$id}" name="{$name}" value="{$value}" {$checked}><label for="{$id}"> {$label}</label><br>