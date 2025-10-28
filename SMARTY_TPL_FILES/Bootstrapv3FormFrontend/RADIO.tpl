{if !isset($checked)}
    {$checked = ''}
{/if}
{if !isset($data_dependency)}
    {$data_dependency = ''}
{/if}
<label for="{$id}"> <input type="radio" value="{$value}" id="{$id}" name="{$name}" {$checked} {$data_dependency}>{$label}</label><br>