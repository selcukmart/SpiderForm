{* Tailwind CSS Form Template *}
{if $stepper_enabled}
    {include file='tailwind/form_stepper.tpl'}
{else}
<form {$form.attributes|attributes nofilter}>
    {if $csrf_token}
        <input type="hidden" name="_csrf_token" value="{$csrf_token}" />
    {/if}

    {foreach $inputs as $input}
        {if isset($input.section) and $input.section}
            {* Render section with its inputs *}
            {if $input.section}
                <div class="form-section mb-6 {$input.section.classes|classes}" {foreach $input.section.attributes as $attr => $attr_value}{$attr}="{$attr_value}" {/foreach}>
                    {if $input.section.collapsible}
                        <details {if !$input.section.collapsed}open{/if} class="border border-gray-300 rounded-lg">
                            <summary class="bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 rounded-t-lg">
                                <h3 class="inline text-lg font-semibold text-gray-900">{$input.section.title}</h3>
                            </summary>
                            <div class="p-4">
                                {if $input.section.description}
                                    <p class="text-gray-600 mb-4">{$input.section.description nofilter}</p>
                                {/if}
                                {if $input.section.htmlContent}
                                    <div class="section-content mb-4">{$input.section.htmlContent nofilter}</div>
                                {/if}
                                {foreach $input.inputs as $section_input}
                                    {foreach $section_input as $key => $value}
                                        {if $key != 'template'}
                                            {assign var=$key value=$value}
                                        {/if}
                                    {/foreach}
                                    {include file=$section_input.template}
                                {/foreach}
                            </div>
                        </details>
                    {else}
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{$input.section.title}</h3>
                        {if $input.section.description}
                            <p class="text-gray-600 mb-4">{$input.section.description nofilter}</p>
                        {/if}
                        {if $input.section.htmlContent}
                            <div class="section-content mb-4">{$input.section.htmlContent nofilter}</div>
                        {/if}
                        <div class="border-l-4 border-indigo-500 pl-4">
                            {foreach $input.inputs as $section_input}
                                {foreach $section_input as $key => $value}
                                    {if $key != 'template'}
                                        {assign var=$key value=$value}
                                    {/if}
                                {/foreach}
                                {include file=$section_input.template}
                            {/foreach}
                        </div>
                    {/if}
                </div>
            {else}
                {* Section is null - render inputs without section wrapper *}
                {foreach $input.inputs as $section_input}
                    {foreach $section_input as $key => $value}
                        {if $key != 'template'}
                            {assign var=$key value=$value}
                        {/if}
                    {/foreach}
                    {include file=$section_input.template}
                {/foreach}
            {/if}
        {else}
            {* Regular input (no sections used) *}
            {foreach $input as $key => $value}
                {if $key != 'template'}
                    {assign var=$key value=$value}
                {/if}
            {/foreach}
            {include file=$input.template}
        {/if}
    {/foreach}
</form>
{/if}
