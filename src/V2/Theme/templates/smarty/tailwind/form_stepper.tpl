{* Tailwind CSS Form with Stepper Template *}
<form {$form.attributes|attributes nofilter}>
    {if $csrf_token}
        <input type="hidden" name="_csrf_token" value="{$csrf_token}" />
    {/if}

    {* Stepper Container *}
    <div class="stepper stepper-{$stepper_options.layout|default:'horizontal'}" data-stepper="{$form.name}">
        {* Stepper Navigation *}
        <div class="stepper-nav {if $stepper_options.layout == 'vertical'}flex flex-col{else}flex justify-between{/if} mb-8">
            {assign var="step_index" value=0}
            {foreach $inputs as $input}
                {if $input.is_section && $input.section}
                    <div class="stepper-nav-item flex {if $stepper_options.layout == 'vertical'}flex-row{else}flex-col{/if} items-center {if $step_index == $stepper_options.startIndex|default:0}active{/if}" data-stepper-nav="{$step_index}">
                        <div class="stepper-nav-step relative w-10 h-10 flex items-center justify-center bg-gray-200 rounded-full transition-all duration-300 z-10">
                            <div class="stepper-nav-step-number font-semibold text-gray-600">{$step_index + 1}</div>
                            <div class="stepper-nav-step-icon hidden text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="stepper-nav-label {if $stepper_options.layout == 'horizontal'}mt-2 text-center{else}ml-4 text-left{/if}">
                            <div class="stepper-nav-title font-semibold text-gray-900 text-sm mb-1">{$input.section.title}</div>
                            {if $input.section.description}
                                <div class="stepper-nav-desc text-gray-500 text-xs">{$input.section.description nofilter}</div>
                            {/if}
                        </div>
                    </div>
                    {if !$smarty.foreach.inputs@last}
                        <div class="stepper-nav-line {if $stepper_options.layout == 'vertical'}w-0.5 h-8 ml-5{else}flex-1 h-0.5 self-center mx-2{/if} bg-gray-200"></div>
                    {/if}
                    {assign var="step_index" value=$step_index+1}
                {/if}
            {/foreach}
        </div>

        {* Stepper Steps Content *}
        <div class="stepper-content">
            {assign var="step_index" value=0}
            {foreach $inputs as $input}
                {if $input.is_section}
                    {* Render section as a step *}
                    {if $input.section}
                        <div class="stepper-step transition-opacity duration-300 {if $step_index == $stepper_options.startIndex|default:0}active opacity-100{else}opacity-0{/if}" data-stepper-step="{$step_index}" style="display: {if $step_index == $stepper_options.startIndex|default:0}block{else}none{/if};">
                            <div class="mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{$input.section.title}</h3>
                                {if $input.section.description}
                                    <p class="text-gray-600">{$input.section.description nofilter}</p>
                                {/if}
                                {if $input.section.htmlContent}
                                    <div class="section-content mb-4">{$input.section.htmlContent nofilter}</div>
                                {/if}
                            </div>
                            <div class="stepper-step-fields space-y-4">
                                {foreach $input.inputs as $section_input}
                                    {foreach $section_input as $key => $value}
                                        {if $key != 'template'}
                                            {assign var=$key value=$value}
                                        {/if}
                                    {/foreach}
                                    {include file=$section_input.template}
                                {/foreach}
                            </div>
                        </div>
                        {assign var="step_index" value=$step_index+1}
                    {else}
                        {* Section is null - render inputs without section wrapper *}
                        <div class="stepper-step" data-stepper-step="0">
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
                {else}
                    {* Regular input (no sections used) - wrap in default step *}
                    {if $smarty.foreach.inputs@first}
                        <div class="stepper-step active" data-stepper-step="0">
                    {/if}
                    {foreach $input as $key => $value}
                        {if $key != 'template'}
                            {assign var=$key value=$value}
                        {/if}
                    {/foreach}
                    {include file=$input.template}
                    {if $smarty.foreach.inputs@last}
                        </div>
                    {/if}
                {/if}
            {/foreach}
        </div>

        {* Stepper Navigation Buttons *}
        {if $stepper_options.showNavigationButtons|default:true}
            <div class="stepper-actions flex justify-between mt-6">
                <button type="button" class="btn-stepper-prev inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50" data-stepper-action="prev">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>
                <div class="space-x-3">
                    <button type="button" class="btn-stepper-next inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-stepper-action="next">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <button type="submit" class="btn-stepper-finish hidden inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" data-stepper-action="finish" style="display: none;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Finish
                    </button>
                </div>
            </div>
        {/if}
    </div>
</form>

<style>
/* Stepper Navigation */
.stepper-nav-item {
    position: relative;
}

.stepper-nav-item.active .stepper-nav-step {
    background-color: #4f46e5;
}

.stepper-nav-item.active .stepper-nav-step-number {
    color: #fff;
}

.stepper-nav-item.completed .stepper-nav-step {
    background-color: #10b981;
}

.stepper-nav-item.completed .stepper-nav-step-number {
    display: none;
}

.stepper-nav-item.completed .stepper-nav-step-icon {
    display: block;
}

.stepper-nav-item.error .stepper-nav-step {
    background-color: #ef4444;
}

/* Clickable steps in non-linear mode */
[data-stepper-nav] {
    cursor: default;
}

.stepper[data-stepper].non-linear [data-stepper-nav] {
    cursor: pointer;
}

.stepper[data-stepper].non-linear [data-stepper-nav]:hover .stepper-nav-step {
    background-color: #4f46e5;
}

.stepper[data-stepper].non-linear [data-stepper-nav]:hover .stepper-nav-step-number {
    color: #fff;
}
</style>
