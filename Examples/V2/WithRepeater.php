<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Example: Repeater Fields
 * 
 * This example demonstrates:
 * 1. Dynamic add/remove rows
 * 2. Min/max row constraints
 * 3. Pre-populated data
 * 4. Complex field groups
 */

// Example 1: Contact List (Simple)
$form1 = FormBuilder::create('contact-list')
    ->setAction('/save-contacts')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Emergency Contacts', 'Add one or more emergency contacts')
    
    ->addRepeater('contacts', 'Contact List', function($repeater) {
        $repeater->addText('name', 'Full Name')
            ->required()
            ->placeholder('John Doe')
            ->add();
        
        $repeater->addTel('phone', 'Phone Number')
            ->required()
            ->placeholder('+1 (555) 123-4567')
            ->add();
        
        $repeater->addSelect('relationship', 'Relationship')
            ->options([
                'spouse' => 'Spouse',
                'parent' => 'Parent',
                'sibling' => 'Sibling',
                'friend' => 'Friend',
                'other' => 'Other'
            ])
            ->add();
    })
    ->minRows(1)
    ->maxRows(5)
    ->helpText('You can add up to 5 emergency contacts. At least 1 is required.')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Contacts')
    ->build();

// Example 2: Work Experience (Complex)
$form2 = FormBuilder::create('work-experience')
    ->setAction('/save-experience')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Work Experience', 'Add your employment history')
    
    ->addRepeater('experience', 'Employment History', function($repeater) {
        $repeater->addText('company', 'Company Name')
            ->required()
            ->add();
        
        $repeater->addText('position', 'Job Title')
            ->required()
            ->add();
        
        $repeater->addDate('start_date', 'Start Date')
            ->required()
            ->add();
        
        $repeater->addDate('end_date', 'End Date')
            ->helpText('Leave blank if current position')
            ->add();
        
        $repeater->addCheckbox('current', 'I currently work here')
            ->add();
        
        $repeater->addTextarea('description', 'Job Description')
            ->rows(3)
            ->helpText('Briefly describe your responsibilities')
            ->add();
    })
    ->minRows(0)
    ->maxRows(10)
    ->helpText('Add your work experience. You can add up to 10 positions.')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Experience')
    ->build();

// Example 3: Education with Min Rows
$form3 = FormBuilder::create('education')
    ->setAction('/save-education')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Education', 'Add your educational background (minimum 1 required)')
    
    ->addRepeater('education', 'Schools/Universities', function($repeater) {
        $repeater->addText('institution', 'Institution Name')
            ->required()
            ->add();
        
        $repeater->addSelect('degree', 'Degree Type')
            ->options([
                'high_school' => 'High School Diploma',
                'associate' => "Associate's Degree",
                'bachelor' => "Bachelor's Degree",
                'master' => "Master's Degree",
                'doctorate' => 'Doctorate/PhD',
                'certificate' => 'Certificate'
            ])
            ->required()
            ->add();
        
        $repeater->addText('field', 'Field of Study')
            ->add();
        
        $repeater->addNumber('graduation_year', 'Graduation Year')
            ->min(1950)
            ->max(2030)
            ->add();
    })
    ->minRows(1)
    ->maxRows(5)
    ->helpText('At least one educational entry is required.')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Education')
    ->build();

// Example 4: Skills with Categories
$form4 = FormBuilder::create('skills')
    ->setAction('/save-skills')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Skills & Proficiency', 'Rate your skills')
    
    ->addRepeater('skills', 'Your Skills', function($repeater) {
        $repeater->addText('skill_name', 'Skill')
            ->required()
            ->placeholder('e.g., PHP, JavaScript, Project Management')
            ->add();
        
        $repeater->addSelect('category', 'Category')
            ->options([
                'technical' => 'Technical',
                'soft' => 'Soft Skills',
                'language' => 'Language',
                'tools' => 'Tools & Software'
            ])
            ->add();
        
        $repeater->addSelect('proficiency', 'Proficiency Level')
            ->options([
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
                'expert' => 'Expert'
            ])
            ->required()
            ->add();
        
        $repeater->addNumber('years', 'Years of Experience')
            ->min(0)
            ->max(50)
            ->add();
    })
    ->minRows(0)
    ->maxRows(20)
    ->helpText('Add as many skills as relevant (up to 20).')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Skills')
    ->build();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repeater Field Examples</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 40px 0;
            background: #f8f9fa;
        }
        .example-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .example-title {
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .feature-badge {
            display: inline-block;
            background: #e7f3ff;
            color: #0056b3;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4">Repeater Fields</h1>
            <p class="lead text-muted">Dynamic add/remove field groups - like jquery.repeater</p>
        </div>

        <!-- Example 1 -->
        <div class="example-section">
            <h2 class="example-title">Example 1: Simple Contact List</h2>
            <div class="feature-badge">Min: 1 Row</div>
            <div class="feature-badge">Max: 5 Rows</div>
            <div class="feature-badge">Simple Fields</div>
            
            <div class="alert alert-info">
                <strong>Features:</strong>
                <ul class="mb-0">
                    <li>Minimum 1 contact required (can't remove last one)</li>
                    <li>Maximum 5 contacts allowed</li>
                    <li>Click "Add Item" to add new contacts</li>
                    <li>Click "Remove" to delete a contact</li>
                </ul>
            </div>
            
            <?= $form1 ?>
        </div>

        <!-- Example 2 -->
        <div class="example-section">
            <h2 class="example-title">Example 2: Work Experience</h2>
            <div class="feature-badge">Max: 10 Rows</div>
            <div class="feature-badge">Complex Fields</div>
            <div class="feature-badge">Conditional Logic</div>
            
            <div class="alert alert-info">
                <strong>Features:</strong>
                <ul class="mb-0">
                    <li>Optional: Start with 0 rows</li>
                    <li>Add up to 10 work experiences</li>
                    <li>Mix of different field types</li>
                    <li>Each row has multiple fields</li>
                </ul>
            </div>
            
            <?= $form2 ?>
        </div>

        <!-- Example 3 -->
        <div class="example-section">
            <h2 class="example-title">Example 3: Education (Required)</h2>
            <div class="feature-badge">Min: 1 Row</div>
            <div class="feature-badge">Auto-initialized</div>
            <div class="feature-badge">Required</div>
            
            <div class="alert alert-info">
                <strong>Features:</strong>
                <ul class="mb-0">
                    <li>Starts with 1 row automatically (minRows: 1)</li>
                    <li>At least 1 education entry must remain</li>
                    <li>Remove button disabled when only 1 row exists</li>
                </ul>
            </div>
            
            <?= $form3 ?>
        </div>

        <!-- Example 4 -->
        <div class="example-section">
            <h2 class="example-title">Example 4: Skills Rating</h2>
            <div class="feature-badge">Max: 20 Rows</div>
            <div class="feature-badge">Rating System</div>
            <div class="feature-badge">Categorized</div>
            
            <div class="alert alert-info">
                <strong>Features:</strong>
                <ul class="mb-0">
                    <li>Add up to 20 skills</li>
                    <li>Categorize and rate each skill</li>
                    <li>Track years of experience</li>
                </ul>
            </div>
            
            <?= $form4 ?>
        </div>

        <div class="alert alert-success">
            <h5>JavaScript API</h5>
            <p>Each repeater exposes a global API for programmatic control:</p>
            <pre class="mb-0"><code>// Add a row programmatically
Repeater_contact_list.addRow();

// Get all data as array
const data = Repeater_contact_list.getData();
console.log(data); // [{name: 'John', phone: '555-1234', ...}, ...]

// Listen to events
document.querySelector('[data-repeater="contact-list"]')
    .addEventListener('repeater:add', (e) => {
        console.log('Row added:', e.detail.index);
    });

document.querySelector('[data-repeater="contact-list"]')
    .addEventListener('repeater:remove', (e) => {
        console.log('Row removed, count:', e.detail.count);
    });</code></pre>
        </div>

        <div class="alert alert-warning">
            <h5>Inspired By</h5>
            <p class="mb-0">This repeater implementation is inspired by popular libraries:</p>
            <ul>
                <li><a href="https://github.com/DubFriend/jquery.repeater" target="_blank">jquery.repeater</a></li>
                <li><a href="https://github.com/Brutenis/Repeater-Field-JS" target="_blank">Repeater-Field-JS</a></li>
                <li><a href="https://codyhouse.co/ds/components/info/repeater" target="_blank">CodyHouse Repeater</a></li>
            </ul>
            <p class="mb-0"><strong>Key Difference:</strong> No jQuery required! Pure vanilla JavaScript with modern ES6+ features.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
