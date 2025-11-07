<?php

/**
 * SpiderForm V2 - Smarty Renderer Example
 *
 * This example demonstrates how to properly configure SmartyRenderer
 * with the correct template paths
 *
 * @author selcukmart
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\SmartyRenderer;
use SpiderForm\V2\Theme\Bootstrap3Theme;
use SpiderForm\V2\Security\SecurityManager;

// IMPORTANT: SmartyRenderer requires the template directory to be specified
// The templates are located in: src/V2/Theme/templates
$renderer = new SmartyRenderer(
    templateDir: __DIR__ . '/../../src/V2/Theme/templates',
    compileDir: sys_get_temp_dir() . '/smarty_compile',
    cacheDir: sys_get_temp_dir() . '/smarty_cache'
);

// If you're using this from your application's vendor folder:
// $renderer = new SmartyRenderer(
//     templateDir: __DIR__ . '/../../vendor/selcukmart/spider-form/src/V2/Theme/templates'
// );

$theme = new Bootstrap3Theme();
$security = new SecurityManager();

// Build form using Chain Pattern
$form = FormBuilder::create('contact_form')
    ->setAction('/contact/submit')
    ->setMethod('POST')
    ->setRenderer($renderer)
    ->setTheme($theme)
    ->setSecurity($security)

    // Section: Contact Information
    ->addSection('Contact Information', 'Please fill out your contact details')

    // Full Name
    ->addText('name', 'Full Name')
        ->required()
        ->minLength(3)
        ->maxLength(100)
        ->placeholder('Enter your full name')
        ->add()

    // Email
    ->addEmail('email', 'Email Address')
        ->required()
        ->placeholder('you@example.com')
        ->add()

    // Phone
    ->addTel('phone', 'Phone Number')
        ->placeholder('+1 (555) 123-4567')
        ->add()

    // Section: Message
    ->addSection('Your Message')

    // Subject
    ->addSelect('subject', 'Subject')
        ->required()
        ->options([
            'general' => 'General Inquiry',
            'support' => 'Technical Support',
            'sales' => 'Sales Question',
            'feedback' => 'Feedback',
        ])
        ->add()

    // Message
    ->addTextarea('message', 'Message')
        ->required()
        ->minLength(10)
        ->maxLength(1000)
        ->rows(5)
        ->placeholder('Type your message here...')
        ->add()

    // Contact Preference
    ->addRadio('contact_preference', 'Preferred Contact Method')
        ->required()
        ->options([
            'email' => 'Email',
            'phone' => 'Phone',
            'either' => 'Either'
        ])
        ->add()

    // Newsletter
    ->addCheckbox('newsletter', 'Subscribe to our newsletter')
        ->defaultValue(false)
        ->add()

    // Submit Button
    ->addSubmit('send', 'Send Message')

    ->build();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form->submit($_POST);

    if ($form->isValid()) {
        $data = $form->getData();

        // Process the validated data
        echo '<div class="alert alert-success">';
        echo 'Thank you! Your message has been sent successfully.';
        echo '</div>';

        // In production, you would:
        // - Send an email
        // - Save to database
        // - Redirect to success page
        // header('Location: /contact/success');
        // exit;

    } else {
        // Get validation errors
        $errors = $form->getErrorList(deep: true);

        echo '<div class="alert alert-danger">';
        echo '<strong>Please fix the following errors:</strong><ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error->getMessage()) . '</li>';
        }
        echo '</ul></div>';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form - SpiderForm V2 with Smarty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Contact Us</h3>
                    </div>
                    <div class="panel-body">
                        <?= $form ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
