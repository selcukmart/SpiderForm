{* Bootstrap 5 Hidden Input *}
<input
    type="hidden"
    {$attributes|attributes nofilter}
    {if $value}value="{$value}"{/if}
/>
