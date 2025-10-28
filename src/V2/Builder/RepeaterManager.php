<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * Repeater Manager
 *
 * Generates JavaScript code for managing repeatable field groups
 * (dynamic add/remove rows like jquery.repeater)
 *
 * @author selcukmart
 * @since 2.0.0
 */
class RepeaterManager
{
    private static array $renderedRepeaters = [];

    /**
     * Generate repeater JavaScript for a field
     *
     * @param string $repeaterId Unique repeater identifier
     * @param int $minRows Minimum number of rows
     * @param int $maxRows Maximum number of rows
     * @param bool $includeScript Whether to wrap in <script> tags
     */
    public static function generateScript(
        string $repeaterId,
        int $minRows = 0,
        int $maxRows = 10,
        bool $includeScript = true
    ): string {
        // Check if already rendered for this repeater
        if (isset(self::$renderedRepeaters[$repeaterId])) {
            return ''; // Already rendered, return empty
        }

        // Mark as rendered
        self::$renderedRepeaters[$repeaterId] = true;

        $script = self::getJavaScriptCode($repeaterId, $minRows, $maxRows);

        if ($includeScript) {
            return sprintf('<script type="text/javascript">%s</script>', $script);
        }

        return $script;
    }

    /**
     * Check if script was already rendered for a repeater
     */
    public static function isRendered(string $repeaterId): bool
    {
        return isset(self::$renderedRepeaters[$repeaterId]);
    }

    /**
     * Reset rendered repeaters tracker (useful for testing)
     */
    public static function reset(): void
    {
        self::$renderedRepeaters = [];
    }

    /**
     * Get the pure JavaScript code
     */
    private static function getJavaScriptCode(string $repeaterId, int $minRows, int $maxRows): string
    {
        // Create a unique namespace for this repeater
        $namespace = 'Repeater_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $repeaterId);

        return <<<JAVASCRIPT

(function() {
    'use strict';

    /**
     * FormGenerator V2 Repeater Manager
     * Repeater ID: {$repeaterId}
     * Similar to jquery.repeater, jquery-repeater-field, and CodyHouse repeater
     */
    const {$namespace} = {
        repeaterSelector: '[data-repeater="{$repeaterId}"]',
        minRows: {$minRows},
        maxRows: {$maxRows},
        rowCount: 0,
        rowIndex: 0,

        /**
         * Initialize repeater
         */
        init: function() {
            const container = document.querySelector(this.repeaterSelector);
            if (!container) {
                console.warn('Repeater: Container not found:', this.repeaterSelector);
                return;
            }

            // Count existing rows
            this.rowCount = container.querySelectorAll('[data-repeater-item]').length;
            this.rowIndex = this.rowCount;

            // Attach event listener to add button
            const addButton = container.querySelector('[data-repeater-add]');
            if (addButton) {
                addButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.addRow();
                });
            }

            // Attach event listeners to existing remove buttons
            this.attachRemoveListeners(container);

            // Update button states
            this.updateButtonStates();

            // Ensure minimum rows
            if (this.minRows > 0 && this.rowCount < this.minRows) {
                for (let i = this.rowCount; i < this.minRows; i++) {
                    this.addRow();
                }
            }
        },

        /**
         * Add a new row
         */
        addRow: function() {
            if (this.rowCount >= this.maxRows) {
                alert('Maximum number of items reached (' + this.maxRows + ')');
                return;
            }

            const container = document.querySelector(this.repeaterSelector);
            if (!container) return;

            // Get template
            const template = container.querySelector('[data-repeater-template]');
            if (!template) {
                console.error('Repeater: Template not found');
                return;
            }

            // Clone template
            const clone = template.cloneNode(true);
            clone.removeAttribute('data-repeater-template');
            clone.setAttribute('data-repeater-item', '');
            clone.style.display = '';

            // Update field names and IDs with index
            const index = this.rowIndex;
            this.updateFieldAttributes(clone, index);

            // Clear field values
            this.clearFieldValues(clone);

            // Insert before add button container
            const addButtonContainer = container.querySelector('[data-repeater-add-container]');
            if (addButtonContainer) {
                container.insertBefore(clone, addButtonContainer);
            } else {
                container.appendChild(clone);
            }

            // Attach remove listener
            this.attachRemoveListener(clone);

            // Animate in
            this.animateIn(clone);

            // Update counters
            this.rowCount++;
            this.rowIndex++;

            // Update button states
            this.updateButtonStates();

            // Trigger custom event
            container.dispatchEvent(new CustomEvent('repeater:add', {
                detail: { row: clone, index: index }
            }));
        },

        /**
         * Remove a row
         */
        removeRow: function(rowElement) {
            if (this.rowCount <= this.minRows) {
                alert('Minimum number of items required (' + this.minRows + ')');
                return;
            }

            const container = document.querySelector(this.repeaterSelector);

            // Animate out
            this.animateOut(rowElement, () => {
                rowElement.remove();
                this.rowCount--;
                this.updateButtonStates();

                // Trigger custom event
                if (container) {
                    container.dispatchEvent(new CustomEvent('repeater:remove', {
                        detail: { count: this.rowCount }
                    }));
                }
            });
        },

        /**
         * Update field attributes with index
         */
        updateFieldAttributes: function(container, index) {
            const fields = container.querySelectorAll('input, select, textarea');
            fields.forEach((field) => {
                // Update name: name="field" -> name="field[0]"
                if (field.name) {
                    const baseName = field.name.replace(/\[\d*\]$/, '');
                    field.name = baseName + '[' + index + ']';
                }

                // Update ID: id="field" -> id="field-0"
                if (field.id) {
                    const baseId = field.id.replace(/-\d+$/, '');
                    field.id = baseId + '-' + index;
                }

                // Update label for attributes
                const label = container.querySelector('label[for="' + field.id.replace(/-\d+$/, '') + '"]');
                if (label && field.id) {
                    label.setAttribute('for', field.id);
                }
            });

            // Update row number display if exists
            const rowNumber = container.querySelector('[data-repeater-row-number]');
            if (rowNumber) {
                rowNumber.textContent = '#' + (index + 1);
            }
        },

        /**
         * Clear field values
         */
        clearFieldValues: function(container) {
            const fields = container.querySelectorAll('input, select, textarea');
            fields.forEach((field) => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = false;
                } else if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else if (field.type !== 'hidden') {
                    field.value = '';
                }
            });
        },

        /**
         * Attach remove listeners to all rows
         */
        attachRemoveListeners: function(container) {
            const rows = container.querySelectorAll('[data-repeater-item]');
            rows.forEach((row) => this.attachRemoveListener(row));
        },

        /**
         * Attach remove listener to a single row
         */
        attachRemoveListener: function(row) {
            const removeButton = row.querySelector('[data-repeater-remove]');
            if (removeButton) {
                removeButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.removeRow(row);
                });
            }
        },

        /**
         * Update button states (disable add if max reached, disable remove if min reached)
         */
        updateButtonStates: function() {
            const container = document.querySelector(this.repeaterSelector);
            if (!container) return;

            const addButton = container.querySelector('[data-repeater-add]');
            if (addButton) {
                addButton.disabled = this.rowCount >= this.maxRows;
            }

            const removeButtons = container.querySelectorAll('[data-repeater-remove]');
            removeButtons.forEach((button) => {
                button.disabled = this.rowCount <= this.minRows;
            });
        },

        /**
         * Animate row in (fade + slide)
         */
        animateIn: function(element) {
            element.style.opacity = '0';
            element.style.maxHeight = '0';
            element.style.overflow = 'hidden';
            element.style.transition = 'opacity 300ms ease-in, max-height 300ms ease-in';

            setTimeout(() => {
                element.style.opacity = '1';
                element.style.maxHeight = '2000px'; // Arbitrary large value
                
                setTimeout(() => {
                    element.style.maxHeight = '';
                    element.style.overflow = '';
                }, 300);
            }, 10);
        },

        /**
         * Animate row out (fade + slide)
         */
        animateOut: function(element, callback) {
            element.style.transition = 'opacity 300ms ease-out, max-height 300ms ease-out';
            element.style.opacity = '0';
            element.style.maxHeight = '0';
            element.style.overflow = 'hidden';

            setTimeout(() => {
                if (callback) callback();
            }, 300);
        },

        /**
         * Get all row data as array
         */
        getData: function() {
            const container = document.querySelector(this.repeaterSelector);
            if (!container) return [];

            const rows = container.querySelectorAll('[data-repeater-item]:not([data-repeater-template])');
            const data = [];

            rows.forEach((row) => {
                const rowData = {};
                const fields = row.querySelectorAll('input, select, textarea');
                
                fields.forEach((field) => {
                    if (field.name) {
                        const name = field.name.replace(/\[\d+\]/, '').replace(/\[|\]/g, '');
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            if (field.checked) {
                                rowData[name] = field.value;
                            }
                        } else {
                            rowData[name] = field.value;
                        }
                    }
                });

                data.push(rowData);
            });

            return data;
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
