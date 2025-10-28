<?php

declare(strict_types=1);

namespace FormGenerator\V2\Event;

/**
 * Form Events - Constants for form lifecycle events
 *
 * These events allow you to hook into the form lifecycle and modify
 * form data, add validation, or perform other actions at specific points.
 *
 * Event Order:
 * 1. PRE_SET_DATA - Before data is set to the form
 * 2. POST_SET_DATA - After data is set to the form
 * 3. PRE_SUBMIT - Before submitted data is processed
 * 4. SUBMIT - During form submission
 * 5. POST_SUBMIT - After form submission
 * 6. PRE_BUILD - Before form HTML is built
 * 7. POST_BUILD - After form HTML is built
 *
 * Example:
 * ```php
 * $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
 *     $data = $event->getData();
 *     // Modify data before it's set to form
 *     $event->setData($modifiedData);
 * });
 * ```
 */
final class FormEvents
{
    /**
     * Triggered before data is set to the form
     *
     * Use this event to:
     * - Modify data before it populates the form
     * - Add/remove form fields based on data
     * - Set default values
     */
    public const PRE_SET_DATA = 'form.pre_set_data';

    /**
     * Triggered after data is set to the form
     *
     * Use this event to:
     * - Validate data
     * - Transform data after it's set
     * - Log data changes
     */
    public const POST_SET_DATA = 'form.post_set_data';

    /**
     * Triggered before submitted data is processed
     *
     * Use this event to:
     * - Modify submitted data before validation
     * - Add additional fields based on submitted data
     * - Sanitize input
     */
    public const PRE_SUBMIT = 'form.pre_submit';

    /**
     * Triggered during form submission
     *
     * Use this event to:
     * - Perform validation
     * - Transform submitted data
     * - Check business rules
     */
    public const SUBMIT = 'form.submit';

    /**
     * Triggered after form submission
     *
     * Use this event to:
     * - Save data to database
     * - Send notifications
     * - Redirect user
     * - Log submission
     */
    public const POST_SUBMIT = 'form.post_submit';

    /**
     * Triggered before form HTML is built
     *
     * Use this event to:
     * - Modify form builder configuration
     * - Add/remove fields dynamically
     * - Change form attributes
     */
    public const PRE_BUILD = 'form.pre_build';

    /**
     * Triggered after form HTML is built
     *
     * Use this event to:
     * - Modify generated HTML
     * - Add custom JavaScript
     * - Inject additional content
     */
    public const POST_BUILD = 'form.post_build';

    /**
     * Triggered on validation errors
     *
     * Use this event to:
     * - Handle validation errors
     * - Send error notifications
     * - Log validation failures
     */
    public const VALIDATION_ERROR = 'form.validation_error';

    /**
     * Triggered when form is successfully validated
     *
     * Use this event to:
     * - Perform post-validation actions
     * - Prepare data for saving
     * - Send success notifications
     */
    public const VALIDATION_SUCCESS = 'form.validation_success';

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
    }
}
