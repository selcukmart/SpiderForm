{if !isset($editFormAction)}
    {$editFormAction = ''}
{/if}
{if !isset($method)}
    {$method = 'post'}
{/if}

{if !isset($method)}
    {$method = 'post'}
{/if}

{if !isset($enctype)}
    {$enctype = 'multipart/form-data'}
{/if}
<form action="{$editFormAction}" method="{$method}" enctype="{$enctype}" name="{$name}" id="{$id}">
    <div class="form-body">
        {$inputs}
    </div>
    <div class="form-actions">
        {$buttons}
    </div>
</form>