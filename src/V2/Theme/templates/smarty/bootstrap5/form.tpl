{* Bootstrap 5 Form Template *}
{if $stepper_enabled}
    {include file='bootstrap5/form_stepper.tpl'}
{else}
<form {$form.attributes|attributes nofilter}>
    {if $csrf_token}
        <input type="hidden" name="_csrf_token" value="{$csrf_token}" />
    {/if}

    {foreach $inputs as $input}
        {if $input.is_section}
            {* Render section with its inputs *}
            {if $input.section}
                <div class="form-section mb-4 {$input.section.classes|classes}" {foreach $input.section.attributes as $attr => $attr_value}{$attr}="{$attr_value}" {/foreach}>
                    {if $input.section.collapsible}
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">
                                    <button class="btn btn-link w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#section-{$smarty.foreach.inputs.iteration}" aria-expanded="{if !$input.section.collapsed}true{else}false{/if}">
                                        {$input.section.title}
                                    </button>
                                </h4>
                            </div>
                            <div id="section-{$smarty.foreach.inputs.iteration}" class="collapse {if !$input.section.collapsed}show{/if}">
                                <div class="card-body">
                                    {if $input.section.description}
                                        <p class="text-muted mb-3">{$input.section.description nofilter}</p>
                                    {/if}
                                    {if $input.section.htmlContent}
                                        <div class="section-content mb-3">{$input.section.htmlContent nofilter}</div>
                                    {/if}
                                    {foreach $input.inputs as $section_input}
                                        {include file=$section_input.template}
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    {else}
                        <h3 class="form-section-title mb-2">{$input.section.title}</h3>
                        {if $input.section.description}
                            <p class="text-muted mb-3">{$input.section.description nofilter}</p>
                        {/if}
                        {if $input.section.htmlContent}
                            <div class="section-content mb-3">{$input.section.htmlContent nofilter}</div>
                        {/if}
                        {foreach $input.inputs as $section_input}
                            {include file=$section_input.template}
                        {/foreach}
                    {/if}
                </div>
            {else}
                {* Section is null - render inputs without section wrapper *}
                {foreach $input.inputs as $section_input}
                    {include file=$section_input.template}
                {/foreach}
            {/if}
        {else}
            {* Regular input (no sections used) *}
            {include file=$input.template}
        {/if}
    {/foreach}
</form>
{/if}
