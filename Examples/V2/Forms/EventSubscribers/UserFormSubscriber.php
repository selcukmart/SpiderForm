<?php

declare(strict_types=1);

namespace FormGenerator\Examples\V2\Forms\EventSubscribers;

use FormGenerator\V2\Event\{EventSubscriberInterface, FormEvent, FormEvents};

/**
 * Example: User Form Event Subscriber
 *
 * Demonstrates how to create event subscribers that listen to
 * multiple form events and modify form data or behavior.
 */
class UserFormSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::POST_SET_DATA => ['onPostSetData', 10], // with priority
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
            FormEvents::PRE_BUILD => 'onPreBuild',
            FormEvents::POST_BUILD => ['onPostBuild', -10], // negative priority = later execution
        ];
    }

    /**
     * Handle PRE_SET_DATA event
     *
     * Use this to modify data before it's set to the form
     */
    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();

        // Example: Add default values
        if (is_array($data) && !isset($data['country'])) {
            $data['country'] = 'US';
            $event->setData($data);
        }

        // Example: Transform data
        if (is_array($data) && isset($data['username'])) {
            $data['username'] = strtolower($data['username']);
            $event->setData($data);
        }

        // Log the event
        error_log("PRE_SET_DATA: Data is being set to form");
    }

    /**
     * Handle POST_SET_DATA event
     *
     * Use this to perform actions after data is set
     */
    public function onPostSetData(FormEvent $event): void
    {
        $data = $event->getData();

        // Example: Validate data
        if (is_array($data) && isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email format: " . $data['email']);
            }
        }

        error_log("POST_SET_DATA: Data has been set to form");
    }

    /**
     * Handle PRE_SUBMIT event
     *
     * Use this to modify submitted data before processing
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        // Example: Sanitize input
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }
            $event->setData($data);
        }

        error_log("PRE_SUBMIT: Form is about to be submitted");
    }

    /**
     * Handle POST_SUBMIT event
     *
     * Use this to perform actions after form submission
     */
    public function onPostSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        // Example: Log submission
        error_log("POST_SUBMIT: Form submitted with data: " . json_encode($data));

        // Example: Send notification
        // Mail::send(...);

        // Example: Save to database
        // DB::table('users')->insert($data);
    }

    /**
     * Handle PRE_BUILD event
     *
     * Use this to modify form before HTML is generated
     */
    public function onPreBuild(FormEvent $event): void
    {
        $form = $event->getForm();

        // Example: Add dynamic fields based on context
        $context = $event->getContext();
        if ($context['show_newsletter'] ?? false) {
            $form->addCheckbox('newsletter', 'Subscribe to newsletter')->add();
        }

        error_log("PRE_BUILD: Form is about to be built");
    }

    /**
     * Handle POST_BUILD event
     *
     * Use this to modify generated HTML
     */
    public function onPostBuild(FormEvent $event): void
    {
        $html = $event->getData();

        if (is_string($html)) {
            // Example: Add custom JavaScript
            $customScript = "\n<script>console.log('Form was built!');</script>";
            $html .= $customScript;
            $event->setData($html);
        }

        error_log("POST_BUILD: Form HTML has been generated");
    }
}
