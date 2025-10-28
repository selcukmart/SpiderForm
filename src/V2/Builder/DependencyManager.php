<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * Dependency Manager
 *
 * Generates JavaScript code for managing input dependencies
 * (show/hide inputs based on other input values)
 *
 * @author selcukmart
 * @since 2.0.0
 */
class DependencyManager
{
    private static array $renderedForms = [];
    private static array $animationOptions = [];

    /**
     * Set animation options for a form
     *
     * @param string $formId Form identifier
     * @param array $options Animation options (duration, type, enabled)
     */
    public static function setAnimationOptions(string $formId, array $options): void
    {
        self::$animationOptions[$formId] = array_merge([
            'enabled' => true,
            'duration' => 300,
            'type' => 'fade', // fade, slide, none
            'easing' => 'ease-in-out'
        ], $options);
    }

    /**
     * Get animation options for a form
     */
    private static function getAnimationOptions(string $formId): array
    {
        return self::$animationOptions[$formId] ?? [
            'enabled' => true,
            'duration' => 300,
            'type' => 'fade',
            'easing' => 'ease-in-out'
        ];
    }

    /**
     * Generate dependency JavaScript for a form
     *
     * @param string $formId Unique form identifier
     * @param bool $includeScript Whether to wrap in <script> tags
     */
    public static function generateScript(string $formId, bool $includeScript = true): string
    {
        // Check if already rendered for this form
        if (isset(self::$renderedForms[$formId])) {
            return ''; // Already rendered, return empty
        }

        // Mark as rendered
        self::$renderedForms[$formId] = true;

        $script = self::getJavaScriptCode($formId);

        if ($includeScript) {
            return sprintf('<script type="text/javascript">%s</script>', $script);
        }

        return $script;
    }

    /**
     * Check if script was already rendered for a form
     */
    public static function isRendered(string $formId): bool
    {
        return isset(self::$renderedForms[$formId]);
    }

    /**
     * Reset rendered forms tracker (useful for testing)
     */
    public static function reset(): void
    {
        self::$renderedForms = [];
    }

    /**
     * Get the pure JavaScript code
     */
    private static function getJavaScriptCode(string $formId): string
    {
        // Create a unique namespace for this form
        $namespace = 'FormGen_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $formId);

        // Get animation options
        $animOptions = self::getAnimationOptions($formId);
        $animEnabled = $animOptions['enabled'] ? 'true' : 'false';
        $animDuration = $animOptions['duration'];
        $animType = $animOptions['type'];
        $animEasing = $animOptions['easing'];

        return <<<JAVASCRIPT

(function() {
    'use strict';

    /**
     * FormGenerator V2 Dependency Manager (Enhanced)
     * Form ID: {$formId}
     * Features: Nested Dependencies (A→B→C), Custom Animations
     */
    const {$namespace} = {
        formSelector: '#{$formId}',
        animationConfig: {
            enabled: {$animEnabled},
            duration: {$animDuration},
            type: '{$animType}',
            easing: '{$animEasing}'
        },

        /**
         * Initialize dependency management
         */
        init: function() {
            const form = document.querySelector(this.formSelector);
            if (!form) {
                console.warn('FormGenerator: Form not found:', this.formSelector);
                return;
            }

            // Attach event listeners to dependency controllers
            const dependencyElements = form.querySelectorAll('[data-dependency="true"]');
            dependencyElements.forEach((element) => {
                const eventType = this.getEventType(element);
                element.addEventListener(eventType, () => this.handleDependency(element));

                // Also trigger on load for select elements with pre-selected values
                if (element.tagName === 'SELECT' && element.value) {
                    this.handleDependency(element);
                }
            });

            // Initial dependency detection
            this.detectInitialState(form);
        },

        /**
         * Get appropriate event type for element
         */
        getEventType: function(element) {
            if (element.tagName === 'SELECT') {
                return 'change';
            }
            if (element.type === 'checkbox' || element.type === 'radio') {
                return 'change';
            }
            return 'input';
        },

        /**
         * Detect and set initial state for all dependencies
         */
        detectInitialState: function(form) {
            const dependencyElements = form.querySelectorAll('[data-dependency="true"]');
            dependencyElements.forEach((element) => {
                if (element.type === 'hidden') {
                    this.handleDependency(element);
                } else if (element.tagName === 'SELECT' && element.value !== '') {
                    this.handleDependency(element);
                } else if ((element.type === 'checkbox' || element.type === 'radio') && element.checked) {
                    this.handleDependency(element);
                }
            });
        },

        /**
         * Handle dependency change
         */
        handleDependency: function(element) {
            const group = element.getAttribute('data-dependency-group');
            const field = element.getAttribute('data-dependency-field');

            if (!group || !field) return;

            let isActive = false;
            let fieldIdentifier = field;

            // Determine if dependency is active
            if (element.tagName === 'SELECT') {
                isActive = element.value !== '' && element.value !== null;
                if (isActive) {
                    fieldIdentifier = field + '-' + element.value;
                }
            } else if (element.type === 'checkbox' || element.type === 'radio') {
                isActive = element.checked;
                if (isActive && element.value) {
                    fieldIdentifier = field + '-' + element.value;
                }
            } else if (element.type === 'hidden') {
                isActive = true;
                if (element.value) {
                    fieldIdentifier = field + '-' + element.value;
                }
            }

            // Find and toggle dependent elements
            this.toggleDependents(group, fieldIdentifier, isActive, element);

            // If select value is empty, hide all dependents in this group
            if (element.tagName === 'SELECT' && (element.value === '' || element.value === null)) {
                this.hideAllDependents(group);
            }
        },

        /**
         * Toggle dependent elements (with nested dependency support)
         */
        toggleDependents: function(group, fieldIdentifier, isActive, triggerElement) {
            const form = document.querySelector(this.formSelector);
            if (!form) return;

            const dependents = form.querySelectorAll('[data-dependend-group="' + group + '"]');

            dependents.forEach((dependent) => {
                const dependedValue = dependent.getAttribute('data-dependend');
                if (!dependedValue) return;

                const dependedValues = dependedValue.split(' ');

                // Check if this dependent should be shown
                const shouldShow = dependedValues.includes(fieldIdentifier) || dependedValues.includes('all');

                if (shouldShow && isActive) {
                    this.showElement(dependent);

                    // NESTED DEPENDENCY SUPPORT
                    // Check if this dependent contains dependency controllers
                    // If yes, trigger them to cascade the dependency chain (A→B→C)
                    this.cascadeNestedDependencies(dependent);
                } else if (!shouldShow || !isActive) {
                    this.hideElement(dependent);

                    // When hiding, also hide all nested dependents
                    this.hideNestedDependents(dependent);
                }
            });
        },

        /**
         * Cascade nested dependencies (A→B→C chain)
         * When a dependent element becomes visible, check if it contains
         * dependency controllers and trigger them
         */
        cascadeNestedDependencies: function(container) {
            const dependencyControllers = container.querySelectorAll('[data-dependency="true"]');

            dependencyControllers.forEach((controller) => {
                // Check if this controller has a value/is active
                let shouldTrigger = false;

                if (controller.tagName === 'SELECT') {
                    shouldTrigger = controller.value !== '' && controller.value !== null;
                } else if (controller.type === 'checkbox' || controller.type === 'radio') {
                    shouldTrigger = controller.checked;
                } else if (controller.type === 'hidden') {
                    shouldTrigger = true;
                }

                // Trigger the dependency if active
                if (shouldTrigger) {
                    this.handleDependency(controller);
                }
            });
        },

        /**
         * Hide all nested dependents when parent is hidden
         */
        hideNestedDependents: function(container) {
            const form = document.querySelector(this.formSelector);
            if (!form) return;

            // Find all dependency controllers within this container
            const controllers = container.querySelectorAll('[data-dependency="true"]');

            controllers.forEach((controller) => {
                const group = controller.getAttribute('data-dependency-group');
                if (group) {
                    // Hide all dependents of this group
                    this.hideAllDependents(group);
                }
            });
        },

        /**
         * Hide all dependents in a group
         */
        hideAllDependents: function(group) {
            const form = document.querySelector(this.formSelector);
            if (!form) return;

            const dependents = form.querySelectorAll('[data-dependend-group="' + group + '"]');
            dependents.forEach((dependent) => this.hideElement(dependent));
        },

        /**
         * Show element with animation (respects animation config)
         */
        showElement: function(element) {
            // Enable form inputs first
            this.toggleInputs(element, false);

            if (!this.animationConfig.enabled || this.animationConfig.type === 'none') {
                // No animation
                element.style.display = '';
                element.style.opacity = '1';
                return;
            }

            const duration = this.animationConfig.duration;
            const easing = this.animationConfig.easing;

            if (this.animationConfig.type === 'fade') {
                element.style.display = '';
                element.style.opacity = '0';
                element.style.transition = `opacity ${duration}ms ${easing}`;

                setTimeout(() => {
                    element.style.opacity = '1';
                }, 10);
            } else if (this.animationConfig.type === 'slide') {
                element.style.display = '';
                element.style.maxHeight = '0';
                element.style.overflow = 'hidden';
                element.style.opacity = '0';
                element.style.transition = `max-height ${duration}ms ${easing}, opacity ${duration}ms ${easing}`;

                // Calculate the natural height
                const naturalHeight = element.scrollHeight;

                setTimeout(() => {
                    element.style.maxHeight = naturalHeight + 'px';
                    element.style.opacity = '1';

                    // Remove max-height after animation completes
                    setTimeout(() => {
                        element.style.maxHeight = '';
                        element.style.overflow = '';
                    }, duration);
                }, 10);
            }
        },

        /**
         * Hide element with animation (respects animation config)
         */
        hideElement: function(element) {
            if (!this.animationConfig.enabled || this.animationConfig.type === 'none') {
                // No animation
                element.style.display = 'none';
                this.toggleInputs(element, true);
                return;
            }

            const duration = this.animationConfig.duration;
            const easing = this.animationConfig.easing;

            if (this.animationConfig.type === 'fade') {
                element.style.opacity = '0';
                element.style.transition = `opacity ${duration}ms ${easing}`;

                setTimeout(() => {
                    element.style.display = 'none';
                    this.toggleInputs(element, true);
                }, duration);
            } else if (this.animationConfig.type === 'slide') {
                const currentHeight = element.scrollHeight;
                element.style.maxHeight = currentHeight + 'px';
                element.style.overflow = 'hidden';
                element.style.transition = `max-height ${duration}ms ${easing}, opacity ${duration}ms ${easing}`;

                setTimeout(() => {
                    element.style.maxHeight = '0';
                    element.style.opacity = '0';

                    setTimeout(() => {
                        element.style.display = 'none';
                        element.style.maxHeight = '';
                        element.style.overflow = '';
                        this.toggleInputs(element, true);
                    }, duration);
                }, 10);
            }
        },

        /**
         * Enable/disable and clear inputs within element
         */
        toggleInputs: function(container, disable) {
            const inputs = container.querySelectorAll('input, select, textarea');
            inputs.forEach((input) => {
                if (disable) {
                    input.disabled = true;
                    // Clear value when hiding (except checkboxes/radios)
                    if (input.type !== 'checkbox' && input.type !== 'radio') {
                        input.value = '';
                    } else {
                        input.checked = false;
                    }
                } else {
                    input.disabled = false;
                }
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {$namespace}.init());
    } else {
        {$namespace}.init();
    }
})();

JAVASCRIPT;
    }
}
