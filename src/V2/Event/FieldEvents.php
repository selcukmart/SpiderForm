<?php

declare(strict_types=1);

namespace FormGenerator\V2\Event;

/**
 * Field Events - Constants for field-level events
 *
 * These events allow you to hook into individual field lifecycle and
 * respond to field-specific actions like value changes, show/hide, etc.
 *
 * Event Flow for Dependencies:
 * 1. FIELD_PRE_RENDER - Before field HTML is rendered
 * 2. FIELD_DEPENDENCY_CHECK - Check if field should be visible based on dependencies
 * 3. FIELD_SHOW / FIELD_HIDE - Field visibility changes
 * 4. FIELD_VALUE_CHANGE - Field value changes (triggered by controller field)
 * 5. FIELD_POST_RENDER - After field HTML is rendered
 *
 * Example:
 * ```php
 * $builder->addText('company_name', 'Company Name')
 *     ->dependsOn('user_type', 'business')
 *     ->onShow(function(FieldEvent $event) {
 *         // Called when field becomes visible
 *         $event->getField()->required(true);
 *     })
 *     ->onHide(function(FieldEvent $event) {
 *         // Called when field becomes hidden
 *         $event->getField()->required(false);
 *     })
 *     ->add();
 * ```
 *
 * @author selcukmart
 * @since 2.3.0
 */
final class FieldEvents
{
    /**
     * Triggered when field value changes
     *
     * Use this event to:
     * - Trigger dependent field visibility
     * - Validate value on change
     * - Update related fields
     * - Log value changes
     */
    public const FIELD_VALUE_CHANGE = 'field.value_change';

    /**
     * Triggered when field becomes visible
     *
     * Use this event to:
     * - Make field required when shown
     * - Load dynamic options
     * - Initialize field-specific JavaScript
     * - Log field visibility
     */
    public const FIELD_SHOW = 'field.show';

    /**
     * Triggered when field becomes hidden
     *
     * Use this event to:
     * - Make field optional when hidden
     * - Clear field value
     * - Clean up resources
     * - Update validation rules
     */
    public const FIELD_HIDE = 'field.hide';

    /**
     * Triggered when field is enabled
     *
     * Use this event to:
     * - Update validation rules
     * - Initialize interactions
     * - Show related UI elements
     */
    public const FIELD_ENABLE = 'field.enable';

    /**
     * Triggered when field is disabled
     *
     * Use this event to:
     * - Skip validation
     * - Hide related UI elements
     * - Clear temporary data
     */
    public const FIELD_DISABLE = 'field.disable';

    /**
     * Triggered when field validation occurs
     *
     * Use this event to:
     * - Add custom validation
     * - Modify validation errors
     * - Log validation attempts
     */
    public const FIELD_VALIDATE = 'field.validate';

    /**
     * Triggered before field HTML is rendered
     *
     * Use this event to:
     * - Modify field configuration
     * - Add dynamic attributes
     * - Check dependencies
     * - Conditional field setup
     */
    public const FIELD_PRE_RENDER = 'field.pre_render';

    /**
     * Triggered after field HTML is rendered
     *
     * Use this event to:
     * - Modify generated HTML
     * - Add custom JavaScript
     * - Inject additional content
     */
    public const FIELD_POST_RENDER = 'field.post_render';

    /**
     * Triggered when checking field dependencies
     *
     * Use this event to:
     * - Evaluate dependency conditions
     * - Determine field visibility
     * - Check parent field values
     * - Apply conditional logic
     */
    public const FIELD_DEPENDENCY_CHECK = 'field.dependency_check';

    /**
     * Triggered when dependency condition is met
     *
     * Use this event to:
     * - Show dependent fields
     * - Enable dependent fields
     * - Load dependent data
     * - Update UI
     */
    public const FIELD_DEPENDENCY_MET = 'field.dependency_met';

    /**
     * Triggered when dependency condition is not met
     *
     * Use this event to:
     * - Hide dependent fields
     * - Disable dependent fields
     * - Clear dependent data
     * - Update UI
     */
    public const FIELD_DEPENDENCY_NOT_MET = 'field.dependency_not_met';

    /**
     * Triggered when field value is set/updated
     *
     * Use this event to:
     * - Transform value
     * - Validate format
     * - Update dependent fields
     * - Trigger cascading changes
     */
    public const FIELD_VALUE_SET = 'field.value_set';

    /**
     * Triggered when field options are loaded (select, radio, checkbox)
     *
     * Use this event to:
     * - Filter options based on other fields
     * - Add dynamic options
     * - Sort/group options
     * - Cache options
     */
    public const FIELD_OPTIONS_LOAD = 'field.options_load';

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
    }
}
