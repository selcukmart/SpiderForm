{{-- Tailwind CSS Checkbox Tree Template --}}
<div class="mb-4 {{ $classes['wrapper'] ?? '' }}" {!! \SpiderForm\V2\Renderer\BladeRenderer::renderAttributes($wrapperAttributes ?? []) !!}>
    @if($label ?? false)
        <label class="{{ $classes['label'] ?? '' }}">
            {{ $label }}
            @if($required ?? false)<span class="text-red-500 ml-1">*</span>@endif
        </label>
    @endif

    @if($helpText ?? false)
        <div class="{{ $classes['help'] ?? '' }}">{{ $helpText }}</div>
    @endif

    <div class="checkbox-tree" data-checkbox-tree="{{ $attributes['id'] ?? '' }}" data-tree-mode="{{ $treeMode ?? '' }}">
        @php
            function renderTailwindTreeNode($nodes, $name, $tree_id, $level = 0) {
                echo '<ul class="space-y-1 ' . ($level > 0 ? 'pl-6' : '') . '">';
                foreach ($nodes as $node) {
                    echo '<li>';
                    echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
                    echo '<input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" name="' . htmlspecialchars($name) . '[]" value="' . htmlspecialchars($node['value']) . '"';
                    if ($node['checked'] ?? false) echo ' checked';
                    if ($node['disabled'] ?? false) echo ' disabled';
                    echo ' />';
                    echo '<span class="' . ($level == 0 ? 'font-semibold text-gray-900' : 'text-gray-700') . '">' . htmlspecialchars($node['label']) . '</span>';
                    echo '</label>';
                    if (!empty($node['children'] ?? [])) {
                        renderTailwindTreeNode($node['children'], $name, $tree_id, $level + 1);
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }
            renderTailwindTreeNode($tree ?? [], $attributes['name'] ?? '', $attributes['id'] ?? '', 0);
        @endphp
    </div>
</div>
