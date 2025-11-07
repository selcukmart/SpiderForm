{{-- Bootstrap 5 Checkbox Tree Template --}}
<div class="mb-3 {{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif

    <div class="checkbox-tree" data-checkbox-tree="{{ $attributes['id'] ?? '' }}" data-tree-mode="{{ $treeMode ?? '' }}">
        @php
            function renderTreeNode($nodes, $name, $tree_id, $level = 0) {
                echo '<ul class="list-unstyled ps-' . ($level > 0 ? '4' : '0') . '">';
                foreach ($nodes as $node) {
                    echo '<li class="mb-1">';
                    echo '<label class="form-check-label d-flex align-items-center">';
                    echo '<input type="checkbox" class="form-check-input me-2" name="' . htmlspecialchars($name) . '[]" value="' . htmlspecialchars($node['value']) . '"';
                    if ($node['checked'] ?? false) echo ' checked';
                    if ($node['disabled'] ?? false) echo ' disabled';
                    echo ' />';
                    echo '<span class="' . ($level == 0 ? 'fw-bold' : '') . '">' . htmlspecialchars($node['label']) . '</span>';
                    echo '</label>';
                    if (!empty($node['children'] ?? [])) {
                        renderTreeNode($node['children'], $name, $tree_id, $level + 1);
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }
            renderTreeNode($tree ?? [], $attributes['name'] ?? '', $attributes['id'] ?? '', 0);
        @endphp
    </div>
</div>
