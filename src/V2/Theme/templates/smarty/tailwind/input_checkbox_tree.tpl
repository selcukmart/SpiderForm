{* Tailwind CSS Checkbox Tree Template *}
<div class="mb-4 {$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="{$classes.label}">
            {$label}
            {if $required}<span class="text-red-500 ml-1">*</span>{/if}
        </label>
    {/if}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}

    <div class="checkbox-tree" data-checkbox-tree="{$attributes.id}" data-tree-mode="{$treeMode}">
        {function name=render_tree_node nodes=$tree name=$attributes.name tree_id=$attributes.id level=0}
            <ul class="space-y-1 {if $level > 0}pl-6{/if}">
                {foreach $nodes as $node}
                    <li>
                        <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                            <input
                                type="checkbox"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                name="{$name}[]"
                                value="{$node.value}"
                                {if $node.checked}checked{/if}
                                {if $node.disabled}disabled{/if}
                            />
                            <span class="{if $level == 0}font-semibold text-gray-900{else}text-gray-700{/if}">
                                {$node.label}
                            </span>
                        </label>
                        {if $node.children}
                            {call name=render_tree_node nodes=$node.children name=$name tree_id=$tree_id level=$level+1}
                        {/if}
                    </li>
                {/foreach}
            </ul>
        {/function}
        {call name=render_tree_node nodes=$tree name=$attributes.name tree_id=$attributes.id level=0}
    </div>
</div>
