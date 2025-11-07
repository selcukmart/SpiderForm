{* Bootstrap 5 Checkbox Tree Template *}
<div class="mb-3 {$classes.wrapper}" {$wrapperAttributes|attributes nofilter}>
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
            <ul class="list-unstyled ps-{if $level > 0}4{else}0{/if}">
                {foreach $nodes as $node}
                    <li class="mb-1">
                        <label class="form-check-label d-flex align-items-center">
                            <input
                                type="checkbox"
                                class="form-check-input me-2"
                                name="{$name}[]"
                                value="{$node.value}"
                                {if $node.checked}checked{/if}
                                {if $node.disabled}disabled{/if}
                            />
                            <span class="{if $level == 0}fw-bold{/if}">{$node.label}</span>
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
