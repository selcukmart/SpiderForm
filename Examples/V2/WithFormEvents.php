<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Event\{FormEvent, FormEvents};
use FormGenerator\Examples\V2\Forms\EventSubscribers\UserFormSubscriber;

/**
 * FormGenerator V2 - Event-Driven Architecture Examples
 *
 * Demonstrates how to use event listeners and subscribers to hook
 * into the form lifecycle and modify behavior.
 */

// Initialize renderer and theme
$renderer = new TwigRenderer(__DIR__ . '/../../src/V2/Theme/templates');
$theme = new Bootstrap5Theme();

echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>\n";
echo "<div class='container my-5'>\n";

echo "<h1 class='mb-4'>Event-Driven Architecture</h1>";
echo "<p class='lead'>Hook into form lifecycle with events</p>";

// =============================================================================
// Example 1: Basic Event Listener
// =============================================================================
echo "<h2 class='mb-4'>Example 1: Basic Event Listener</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Add a simple event listener to modify data:</p>";

$form1 = FormBuilder::create('user_form')
    ->setRenderer($renderer)
    ->setTheme($theme)

    // Add event listener
    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        $data = $event->getData();
        if (is_array($data)) {
            // Automatically set default country
            $data['country'] = $data['country'] ?? 'US';
            $event->setData($data);
        }
        echo "<div class='alert alert-info'>PRE_SET_DATA event triggered!</div>";
    })

    ->addText('username', 'Username')->add()
    ->addSelect('country', 'Country')
        ->options(['US' => 'United States', 'UK' => 'United Kingdom', 'DE' => 'Germany'])
        ->add();

// Set data - will trigger PRE_SET_DATA event
$form1->setData(['username' => 'john_doe']);

echo $form1->build();

echo "</div></div>";

// =============================================================================
// Example 2: POST_BUILD Event - Modify HTML
// =============================================================================
echo "<h2 class='mb-4'>Example 2: POST_BUILD Event - Modify HTML</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Add custom JavaScript after form is built:</p>";

$form2 = FormBuilder::create('enhanced_form')
    ->setRenderer($renderer)
    ->setTheme($theme)

    ->addEventListener(FormEvents::POST_BUILD, function(FormEvent $event) {
        $html = $event->getData();

        // Add custom analytics script
        $analyticsScript = "\n<script>console.log('Form rendered at: " . date('Y-m-d H:i:s') . "');</script>";
        $html .= $analyticsScript;

        $event->setData($html);
    })

    ->addText('search', 'Search')->placeholder('Search...')->add()
    ->addSubmit('search', 'Search');

echo $form2->build();

echo "</div></div>";

// =============================================================================
// Example 3: Event Subscriber
// =============================================================================
echo "<h2 class='mb-4'>Example 3: Event Subscriber</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Use event subscriber to listen to multiple events:</p>";

$form3 = FormBuilder::create('subscriber_form')
    ->setRenderer($renderer)
    ->setTheme($theme)

    // Add subscriber
    ->addSubscriber(new UserFormSubscriber())

    ->addText('username', 'Username')->required()->add()
    ->addEmail('email', 'Email')->required()->add()
    ->addSubmit('submit', 'Submit');

echo $form3->build();

echo "<div class='alert alert-info mt-3'>";
echo "Check your error_log to see event subscriber in action!";
echo "</div>";

echo "</div></div>";

// =============================================================================
// Example 4: Multiple Event Listeners with Priority
// =============================================================================
echo "<h2 class='mb-4'>Example 4: Event Listeners with Priority</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Add multiple listeners with different priorities:</p>";

$form4 = FormBuilder::create('priority_form')
    ->setRenderer($renderer)
    ->setTheme($theme)

    // Lower priority (executed later)
    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        echo "<div class='badge bg-info'>Listener 3 (Priority: -10)</div> ";
    }, -10)

    // Default priority (0)
    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        echo "<div class='badge bg-primary'>Listener 2 (Priority: 0)</div> ";
    })

    // Higher priority (executed first)
    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        echo "<div class='badge bg-success'>Listener 1 (Priority: 10)</div> ";
    }, 10)

    ->addText('name', 'Name')->add();

$form4->setData(['name' => 'Test']);

echo "<p class='mt-3'>Order of execution (highest priority first):</p>";
echo $form4->build();

echo "</div></div>";

// =============================================================================
// Example 5: Stop Event Propagation
// =============================================================================
echo "<h2 class='mb-4'>Example 5: Stop Event Propagation</h2>";
echo "<div class='card mb-5'><div class='card-body'>";
echo "<p>Stop subsequent listeners from being called:</p>";

$form5 = FormBuilder::create('stop_propagation_form')
    ->setRenderer($renderer)
    ->setTheme($theme)

    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        echo "<div class='alert alert-warning'>Listener 1: Stopping propagation!</div>";
        $event->stopPropagation(); // Stop here
    }, 10)

    ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
        echo "<div class='alert alert-danger'>Listener 2: This won't be called!</div>";
    })

    ->addText('test', 'Test')->add();

$form5->setData(['test' => 'value']);

echo $form5->build();

echo "</div></div>";

// =============================================================================
// Example 6: Available Form Events
// =============================================================================
echo "<h2 class='mb-4'>Example 6: Available Form Events</h2>";
echo "<div class='card mb-5'><div class='card-body'>";

echo "<table class='table table-striped'>";
echo "<thead><tr><th>Event</th><th>When</th><th>Use For</th></tr></thead>";
echo "<tbody>";

$events = [
    ['PRE_SET_DATA', 'Before data is set to form', 'Modify data, add default values'],
    ['POST_SET_DATA', 'After data is set to form', 'Validate data, log changes'],
    ['PRE_SUBMIT', 'Before submitted data is processed', 'Sanitize input, modify data'],
    ['SUBMIT', 'During form submission', 'Validate, transform data'],
    ['POST_SUBMIT', 'After form submission', 'Save to DB, send notifications'],
    ['PRE_BUILD', 'Before form HTML is built', 'Add/remove fields dynamically'],
    ['POST_BUILD', 'After form HTML is built', 'Modify HTML, add scripts'],
    ['VALIDATION_ERROR', 'On validation errors', 'Handle errors, send notifications'],
    ['VALIDATION_SUCCESS', 'On successful validation', 'Post-validation actions'],
];

foreach ($events as $event) {
    echo "<tr>";
    echo "<td><code>{$event[0]}</code></td>";
    echo "<td>{$event[1]}</td>";
    echo "<td>{$event[2]}</td>";
    echo "</tr>";
}

echo "</tbody></table>";

echo "</div></div>";

// =============================================================================
// Summary
// =============================================================================
echo "<div class='alert alert-success'>";
echo "<h4>Summary: Event-Driven Architecture</h4>";
echo "<p><strong>Key Features:</strong></p>";
echo "<ul>";
echo "<li><strong>Event Listeners</strong>: Hook into specific events with callbacks</li>";
echo "<li><strong>Event Subscribers</strong>: Listen to multiple events in one class</li>";
echo "<li><strong>Event Priority</strong>: Control execution order</li>";
echo "<li><strong>Stop Propagation</strong>: Prevent subsequent listeners</li>";
echo "<li><strong>9 Form Events</strong>: Cover entire form lifecycle</li>";
echo "<li><strong>Event Data</strong>: Access and modify form data</li>";
echo "<li><strong>Context</strong>: Pass additional data via event context</li>";
echo "</ul>";

echo "<p class='mt-3'><strong>Benefits:</strong></p>";
echo "<ul>";
echo "<li>Separation of concerns</li>";
echo "<li>Reusable event handlers</li>";
echo "<li>Testable event logic</li>";
echo "<li>Flexible architecture</li>";
echo "<li>Plugin-like extensibility</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // End container

echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>\n";
