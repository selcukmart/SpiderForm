{* Tailwind CSS Hidden Input *}
<input
    type="hidden"
    {$attributes|attributes nofilter}
    {if $value}value="{$value}"{/if}
/>
