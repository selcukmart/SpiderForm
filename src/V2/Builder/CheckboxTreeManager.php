<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * Checkbox Tree Manager
 *
 * Generates JavaScript code for managing hierarchical checkbox trees
 * with two modes: cascading and independent selection
 *
 * @author selcukmart
 * @since 2.0.0
 */
class CheckboxTreeManager
{
    private static array $renderedTrees = [];

    public const MODE_CASCADE = 'cascade';
    public const MODE_INDEPENDENT = 'independent';

    /**
     * Generate checkbox tree JavaScript for a tree
     *
     * @param string $treeId Unique tree identifier
     * @param string $mode cascade or independent
     * @param bool $includeScript Whether to wrap in <script> tags
     */
    public static function generateScript(string $treeId, string $mode = self::MODE_CASCADE, bool $includeScript = true): string
    {
        // Check if already rendered for this tree
        if (isset(self::$renderedTrees[$treeId])) {
            return ''; // Already rendered, return empty
        }

        // Mark as rendered
        self::$renderedTrees[$treeId] = true;

        $script = self::getJavaScriptCode($treeId, $mode);

        if ($includeScript) {
            return sprintf('<script type="text/javascript">%s</script>', $script);
        }

        return $script;
    }

    /**
     * Check if script was already rendered for a tree
     */
    public static function isRendered(string $treeId): bool
    {
        return isset(self::$renderedTrees[$treeId]);
    }

    /**
     * Reset rendered trees tracker (useful for testing)
     */
    public static function reset(): void
    {
        self::$renderedTrees = [];
    }

    /**
     * Get the pure JavaScript code
     */
    private static function getJavaScriptCode(string $treeId, string $mode): string
    {
        // Create a unique namespace for this tree
        $namespace = 'CheckboxTree_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $treeId);
        $isCascade = $mode === self::MODE_CASCADE ? 'true' : 'false';

        return <<<JAVASCRIPT

(function() {
    'use strict';

    /**
     * FormGenerator V2 Checkbox Tree Manager
     * Tree ID: {$treeId}
     * Mode: {$mode}
     */
    const {$namespace} = {
        treeSelector: '[data-checkbox-tree="{$treeId}"]',
        mode: '{$mode}',

        /**
         * Initialize checkbox tree
         */
        init: function() {
            const tree = document.querySelector(this.treeSelector);
            if (!tree) {
                console.warn('CheckboxTree: Tree not found:', this.treeSelector);
                return;
            }

            // Attach event listeners to all checkboxes
            const checkboxes = tree.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', (e) => this.handleCheckboxChange(e.target));
            });

            // Set initial state
            this.updateIndeterminateStates();
        },

        /**
         * Handle checkbox change
         */
        handleCheckboxChange: function(checkbox) {
            if (this.mode === 'cascade') {
                this.handleCascadeMode(checkbox);
            } else {
                this.handleIndependentMode(checkbox);
            }
        },

        /**
         * Cascade Mode: Parent ↔ Children synchronization
         */
        handleCascadeMode: function(checkbox) {
            const isChecked = checkbox.checked;
            const listItem = checkbox.closest('li');

            // 1. If parent checkbox changed, update all children
            const childList = listItem.querySelector('ul');
            if (childList) {
                const childCheckboxes = childList.querySelectorAll('input[type="checkbox"]');
                childCheckboxes.forEach((childCheckbox) => {
                    childCheckbox.checked = isChecked;
                });
            }

            // 2. If child checkbox changed, update parent state
            this.updateParentCheckboxes(checkbox);
            
            // 3. Update indeterminate states
            this.updateIndeterminateStates();
        },

        /**
         * Update parent checkboxes based on children state
         */
        updateParentCheckboxes: function(checkbox) {
            let parentLi = checkbox.closest('li').parentElement?.closest('li');
            
            while (parentLi) {
                const parentCheckbox = parentLi.querySelector(':scope > label > input[type="checkbox"]');
                if (parentCheckbox) {
                    const childList = parentLi.querySelector(':scope > ul');
                    if (childList) {
                        const childCheckboxes = Array.from(childList.querySelectorAll(':scope > li > label > input[type="checkbox"]'));
                        const allChecked = childCheckboxes.every(cb => cb.checked);
                        const someChecked = childCheckboxes.some(cb => cb.checked);

                        if (allChecked) {
                            parentCheckbox.checked = true;
                            parentCheckbox.indeterminate = false;
                        } else if (someChecked) {
                            parentCheckbox.checked = false;
                            parentCheckbox.indeterminate = true;
                        } else {
                            parentCheckbox.checked = false;
                            parentCheckbox.indeterminate = false;
                        }
                    }
                }

                parentLi = parentLi.parentElement?.closest('li');
            }
        },

        /**
         * Update indeterminate states for all parent checkboxes
         */
        updateIndeterminateStates: function() {
            if (this.mode !== 'cascade') return;

            const tree = document.querySelector(this.treeSelector);
            if (!tree) return;

            // Find all parent items (items that have child ul)
            const parentItems = tree.querySelectorAll('li:has(ul)');
            
            parentItems.forEach((parentLi) => {
                const parentCheckbox = parentLi.querySelector(':scope > label > input[type="checkbox"]');
                const childList = parentLi.querySelector(':scope > ul');
                
                if (parentCheckbox && childList) {
                    const childCheckboxes = Array.from(childList.querySelectorAll(':scope > li > label > input[type="checkbox"]'));
                    const checkedCount = childCheckboxes.filter(cb => cb.checked).length;
                    const totalCount = childCheckboxes.length;

                    if (checkedCount === 0) {
                        parentCheckbox.indeterminate = false;
                        parentCheckbox.checked = false;
                    } else if (checkedCount === totalCount) {
                        parentCheckbox.indeterminate = false;
                        parentCheckbox.checked = true;
                    } else {
                        parentCheckbox.indeterminate = true;
                        parentCheckbox.checked = false;
                    }
                }
            });
        },

        /**
         * Independent Mode: Each checkbox is independent
         */
        handleIndependentMode: function(checkbox) {
            // In independent mode, no cascading behavior
            // Just handle the single checkbox
            // Nothing special to do here, checkbox works normally
        },

        /**
         * Get all checked values
         */
        getCheckedValues: function() {
            const tree = document.querySelector(this.treeSelector);
            if (!tree) return [];

            const checked = tree.querySelectorAll('input[type="checkbox"]:checked');
            return Array.from(checked).map(cb => cb.value);
        },

        /**
         * Set checked values
         */
        setCheckedValues: function(values) {
            const tree = document.querySelector(this.treeSelector);
            if (!tree) return;

            const checkboxes = tree.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = values.includes(checkbox.value);
            });

            if (this.mode === 'cascade') {
                this.updateIndeterminateStates();
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {$namespace}.init());
    } else {
        {$namespace}.init();
    }

    // Expose API globally for external access
    window.{$namespace} = {$namespace};
})();

JAVASCRIPT;
    }
}
