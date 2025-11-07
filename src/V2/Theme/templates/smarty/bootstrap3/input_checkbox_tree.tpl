{* Bootstrap 3 Checkbox Tree Template *}
<div class="form-group {$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
    {if $label}
        <label class="{$classes.label}">
            {$label}
            {if $required}<span class="text-danger">*</span>{/if}
        </label>
    {/if}

    {if $helpText}
        <div class="{$classes.help}">{$helpText}</div>
    {/if}

    <div class="checkbox-tree" data-checkbox-tree="{$attributes.id}" data-tree-mode="{$treeMode}">
        {function name=render_tree_node nodes=$tree name=$attributes.name tree_id=$attributes.id level=0}
            <ul class="list-unstyled" style="padding-left: {if $level > 0}20px{else}0{/if};">
                {foreach $nodes as $node}
                    <li style="margin-bottom: 5px;">
                        <label class="checkbox-inline">
                            <input
                                type="checkbox"
                                name="{$name}[]"
                                value="{$node.value}"
                                {if $node.checked}checked{/if}
                                {if $node.disabled}disabled{/if}
                            />
                            <span {if $level == 0}style="font-weight: bold;"{/if}>{$node.label}</span>
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
