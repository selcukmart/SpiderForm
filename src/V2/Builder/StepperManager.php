<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

/**
 * Stepper/Wizard Manager
 *
 * Generates JavaScript code for managing multi-step forms (form wizards)
 * Similar to Metronic Stepper, with pure vanilla JavaScript
 *
 * @author selcukmart
 * @since 2.0.0
 */
class StepperManager
{
    private static array $renderedSteppers = [];

    public const LAYOUT_HORIZONTAL = 'horizontal';
    public const LAYOUT_VERTICAL = 'vertical';

    public const MODE_LINEAR = 'linear';
    public const MODE_NON_LINEAR = 'non-linear';

    /**
     * Generate stepper JavaScript
     *
     * @param string $stepperId Unique stepper identifier
     * @param array $options Stepper options
     * @param bool $includeScript Whether to wrap in <script> tags
     */
    public static function generateScript(
        string $stepperId,
        array $options = [],
        bool $includeScript = true
    ): string {
        // Check if already rendered
        if (isset(self::$renderedSteppers[$stepperId])) {
            return '';
        }

        // Mark as rendered
        self::$renderedSteppers[$stepperId] = true;

        $script = self::getJavaScriptCode($stepperId, $options);

        if ($includeScript) {
            return sprintf('<script type="text/javascript">%s</script>', $script);
        }

        return $script;
    }

    /**
     * Check if script was already rendered
     */
    public static function isRendered(string $stepperId): bool
    {
        return isset(self::$renderedSteppers[$stepperId]);
    }

    /**
     * Reset rendered steppers tracker
     */
    public static function reset(): void
    {
        self::$renderedSteppers = [];
    }

    /**
     * Get the pure JavaScript code
     */
    private static function getJavaScriptCode(string $stepperId, array $options): string
    {
        $namespace = 'Stepper_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $stepperId);

        // Default options
        $defaults = [
            'layout' => self::LAYOUT_HORIZONTAL,
            'mode' => self::MODE_LINEAR,
            'startIndex' => 0,
            'animation' => true,
            'animationDuration' => 300,
            'validateOnNext' => true,
            'showNavigationButtons' => true,
        ];

        $opts = array_merge($defaults, $options);

        $layout = $opts['layout'];
        $mode = $opts['mode'];
        $startIndex = $opts['startIndex'];
        $validateOnNext = $opts['validateOnNext'] ? 'true' : 'false';
        $animation = $opts['animation'] ? 'true' : 'false';
        $animationDuration = $opts['animationDuration'];

        return <<<JAVASCRIPT

(function() {
    'use strict';

    /**
     * FormGenerator V2 Stepper Manager
     * Stepper ID: {$stepperId}
     * Layout: {$layout}
     * Mode: {$mode}
     * 
     * Features:
     * - Multi-step form wizard
     * - Horizontal/Vertical layouts
     * - Linear/Non-linear navigation
     * - Step validation
     * - Progress tracking
     * - Customizable styling
     */
    const {$namespace} = {
        stepperSelector: '[data-stepper="{$stepperId}"]',
        currentStep: {$startIndex},
        totalSteps: 0,
        steps: [],
        options: {
            layout: '{$layout}',
            mode: '{$mode}',
            startIndex: {$startIndex},
            validateOnNext: {$validateOnNext},
            animation: {$animation},
            animationDuration: {$animationDuration}
        },

        /**
         * Initialize stepper
         */
        init: function() {
            const container = document.querySelector(this.stepperSelector);
            if (!container) {
                console.warn('Stepper: Container not found:', this.stepperSelector);
                return;
            }

            // Get all steps
            this.steps = Array.from(container.querySelectorAll('[data-stepper-step]'));
            this.totalSteps = this.steps.length;

            if (this.totalSteps === 0) {
                console.warn('Stepper: No steps found');
                return;
            }

            // Initialize navigation
            this.initializeNavigation(container);

            // Show initial step
            this.goTo(this.currentStep, false);

            // Setup step navigation clicks
            this.setupStepNavigation();

            // Trigger init event
            this.triggerEvent('init', { totalSteps: this.totalSteps });
        },

        /**
         * Initialize navigation buttons
         */
        initializeNavigation: function(container) {
            const prevBtn = container.querySelector('[data-stepper-action="prev"]');
            const nextBtn = container.querySelector('[data-stepper-action="next"]');
            const finishBtn = container.querySelector('[data-stepper-action="finish"]');

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.previous();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.next();
                });
            }

            if (finishBtn) {
                finishBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.finish();
                });
            }

            // Update button states
            this.updateNavigationButtons();
        },

        /**
         * Setup clickable step navigation
         */
        setupStepNavigation: function() {
            if (this.options.mode === 'linear') {
                return; // In linear mode, can't click to jump
            }

            const container = document.querySelector(this.stepperSelector);
            const stepIndicators = container.querySelectorAll('[data-stepper-nav]');

            stepIndicators.forEach((indicator, index) => {
                indicator.style.cursor = 'pointer';
                indicator.addEventListener('click', () => {
                    this.goTo(index);
                });
            });
        },

        /**
         * Go to specific step
         */
        goTo: function(stepIndex, animate = true) {
            if (stepIndex < 0 || stepIndex >= this.totalSteps) {
                return false;
            }

            // In linear mode, can only go to completed or current step
            if (this.options.mode === 'linear' && stepIndex > this.currentStep) {
                // Check if all previous steps are completed
                for (let i = this.currentStep; i < stepIndex; i++) {
                    if (!this.isStepCompleted(i)) {
                        console.warn('Stepper: Cannot skip to step', stepIndex, 'in linear mode');
                        return false;
                    }
                }
            }

            const oldStep = this.currentStep;
            this.currentStep = stepIndex;

            // Hide all steps
            this.steps.forEach((step, index) => {
                if (index === stepIndex) {
                    this.showStep(step, animate);
                } else {
                    this.hideStep(step, animate);
                }
            });

            // Update step states
            this.updateStepStates();

            // Update navigation buttons
            this.updateNavigationButtons();

            // Trigger event
            this.triggerEvent('change', {
                from: oldStep,
                to: stepIndex,
                step: this.steps[stepIndex]
            });

            return true;
        },

        /**
         * Go to next step
         */
        next: function() {
            if (this.currentStep >= this.totalSteps - 1) {
                return false;
            }

            // Validate current step if enabled
            if (this.options.validateOnNext) {
                if (!this.validateStep(this.currentStep)) {
                    this.triggerEvent('validation-failed', {
                        step: this.currentStep
                    });
                    return false;
                }
            }

            // Mark current step as completed
            this.markStepCompleted(this.currentStep);

            // Go to next step
            const success = this.goTo(this.currentStep + 1);

            if (success) {
                this.triggerEvent('next', {
                    step: this.currentStep
                });
            }

            return success;
        },

        /**
         * Go to previous step
         */
        previous: function() {
            if (this.currentStep <= 0) {
                return false;
            }

            const success = this.goTo(this.currentStep - 1);

            if (success) {
                this.triggerEvent('previous', {
                    step: this.currentStep
                });
            }

            return success;
        },

        /**
         * Finish wizard
         */
        finish: function() {
            // Validate last step
            if (this.options.validateOnNext) {
                if (!this.validateStep(this.currentStep)) {
                    this.triggerEvent('validation-failed', {
                        step: this.currentStep
                    });
                    return false;
                }
            }

            // Mark last step as completed
            this.markStepCompleted(this.currentStep);

            // Trigger complete event
            this.triggerEvent('complete', {
                totalSteps: this.totalSteps
            });

            // Submit form if exists
            const container = document.querySelector(this.stepperSelector);
            const form = container.closest('form');
            if (form) {
                form.submit();
            }

            return true;
        },

        /**
         * Validate a step
         */
        validateStep: function(stepIndex) {
            const step = this.steps[stepIndex];
            if (!step) return true;

            // Find all required inputs in this step
            const inputs = step.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach((input) => {
                // Skip disabled or hidden inputs
                if (input.disabled || input.offsetParent === null) {
                    return;
                }

                if (!input.checkValidity()) {
                    isValid = false;
                    input.reportValidity();
                }
            });

            return isValid;
        },

        /**
         * Check if step is completed
         */
        isStepCompleted: function(stepIndex) {
            const step = this.steps[stepIndex];
            return step && step.classList.contains('completed');
        },

        /**
         * Mark step as completed
         */
        markStepCompleted: function(stepIndex) {
            const step = this.steps[stepIndex];
            if (step) {
                step.classList.add('completed');
                step.classList.remove('error');

                // Update indicator
                const container = document.querySelector(this.stepperSelector);
                const indicator = container.querySelector(`[data-stepper-nav="${stepIndex}"]`);
                if (indicator) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('error');
                }
            }
        },

        /**
         * Mark step as error
         */
        markStepError: function(stepIndex) {
            const step = this.steps[stepIndex];
            if (step) {
                step.classList.add('error');

                const container = document.querySelector(this.stepperSelector);
                const indicator = container.querySelector(`[data-stepper-nav="${stepIndex}"]`);
                if (indicator) {
                    indicator.classList.add('error');
                }
            }
        },

        /**
         * Update step states (pending/active/completed)
         */
        updateStepStates: function() {
            const container = document.querySelector(this.stepperSelector);

            this.steps.forEach((step, index) => {
                const indicator = container.querySelector(`[data-stepper-nav="${index}"]`);

                if (index === this.currentStep) {
                    step.classList.add('active');
                    if (indicator) indicator.classList.add('active');
                } else {
                    step.classList.remove('active');
                    if (indicator) indicator.classList.remove('active');
                }

                if (index < this.currentStep && !this.isStepCompleted(index)) {
                    this.markStepCompleted(index);
                }
            });
        },

        /**
         * Update navigation button states
         */
        updateNavigationButtons: function() {
            const container = document.querySelector(this.stepperSelector);
            const prevBtn = container.querySelector('[data-stepper-action="prev"]');
            const nextBtn = container.querySelector('[data-stepper-action="next"]');
            const finishBtn = container.querySelector('[data-stepper-action="finish"]');

            // Previous button
            if (prevBtn) {
                prevBtn.disabled = this.currentStep === 0;
            }

            // Next button
            if (nextBtn) {
                if (this.currentStep === this.totalSteps - 1) {
                    nextBtn.style.display = 'none';
                } else {
                    nextBtn.style.display = '';
                    nextBtn.disabled = false;
                }
            }

            // Finish button
            if (finishBtn) {
                if (this.currentStep === this.totalSteps - 1) {
                    finishBtn.style.display = '';
                } else {
                    finishBtn.style.display = 'none';
                }
            }
        },

        /**
         * Show step with animation
         */
        showStep: function(step, animate) {
            if (!this.options.animation || !animate) {
                step.style.display = 'block';
                step.style.opacity = '1';
                return;
            }

            step.style.display = 'block';
            step.style.opacity = '0';
            step.style.transition = `opacity \${this.options.animationDuration}ms ease-in`;

            setTimeout(() => {
                step.style.opacity = '1';
            }, 10);
        },

        /**
         * Hide step with animation
         */
        hideStep: function(step, animate) {
            if (!this.options.animation || !animate) {
                step.style.display = 'none';
                return;
            }

            step.style.opacity = '0';
            step.style.transition = `opacity \${this.options.animationDuration}ms ease-out`;

            setTimeout(() => {
                step.style.display = 'none';
            }, this.options.animationDuration);
        },

        /**
         * Trigger custom event
         */
        triggerEvent: function(eventName, detail) {
            const container = document.querySelector(this.stepperSelector);
            if (container) {
                container.dispatchEvent(new CustomEvent('stepper:' + eventName, {
                    detail: detail
                }));
            }
        },

        /**
         * Get current step index
         */
        getCurrentStep: function() {
            return this.currentStep;
        },

        /**
         * Get total steps
         */
        getTotalSteps: function() {
            return this.totalSteps;
        },

        /**
         * Get progress percentage
         */
        getProgress: function() {
            return Math.round((this.currentStep / (this.totalSteps - 1)) * 100);
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {$namespace}.init());
    } else {
        {$namespace}.init();
    }

    // Expose API globally
    window.{$namespace} = {$namespace};
})();

JAVASCRIPT;
    }
}
