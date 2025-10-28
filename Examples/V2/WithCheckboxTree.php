<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\CheckboxTreeManager;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Example: Checkbox Tree
 * 
 * This example demonstrates:
 * 1. Cascade mode (parent→children synchronization)
 * 2. Independent mode (each checkbox independent)
 * 3. Hierarchical structures
 * 4. Indeterminate states (cascade mode)
 */

// Example tree structure: Permissions
$permissionsTree = [
    [
        'value' => 'users',
        'label' => 'User Management',
        'children' => [
            ['value' => 'users.view', 'label' => 'View Users'],
            ['value' => 'users.create', 'label' => 'Create Users'],
            ['value' => 'users.edit', 'label' => 'Edit Users'],
            ['value' => 'users.delete', 'label' => 'Delete Users'],
        ]
    ],
    [
        'value' => 'content',
        'label' => 'Content Management',
        'children' => [
            ['value' => 'content.view', 'label' => 'View Content'],
            ['value' => 'content.create', 'label' => 'Create Content'],
            ['value' => 'content.edit', 'label' => 'Edit Content'],
            ['value' => 'content.delete', 'label' => 'Delete Content'],
            [
                'value' => 'content.media',
                'label' => 'Media Library',
                'children' => [
                    ['value' => 'content.media.upload', 'label' => 'Upload Media'],
                    ['value' => 'content.media.delete', 'label' => 'Delete Media'],
                ]
            ]
        ]
    ],
    [
        'value' => 'settings',
        'label' => 'System Settings',
        'children' => [
            ['value' => 'settings.general', 'label' => 'General Settings'],
            ['value' => 'settings.security', 'label' => 'Security Settings'],
            ['value' => 'settings.api', 'label' => 'API Settings'],
        ]
    ]
];

// Example 1: Cascade Mode (Default)
$form1 = FormBuilder::create('permissions-cascade')
    ->setAction('/save-permissions')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Role Permissions', 'Select permissions for this role. Checking a parent automatically checks all children.')
    
    ->addCheckboxTree(
        'permissions',
        'Available Permissions',
        $permissionsTree,
        CheckboxTreeManager::MODE_CASCADE
    )
    ->helpText('In cascade mode, selecting a parent selects all children. Deselecting a parent deselects all children.')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Role')
    ->build();

// Example 2: Independent Mode
$categoriesTree = [
    [
        'value' => 'electronics',
        'label' => 'Electronics',
        'children' => [
            ['value' => 'phones', 'label' => 'Mobile Phones'],
            ['value' => 'laptops', 'label' => 'Laptops'],
            ['value' => 'tablets', 'label' => 'Tablets'],
        ]
    ],
    [
        'value' => 'clothing',
        'label' => 'Clothing',
        'children' => [
            [
                'value' => 'mens',
                'label' => "Men's Clothing",
                'children' => [
                    ['value' => 'mens-shirts', 'label' => 'Shirts'],
                    ['value' => 'mens-pants', 'label' => 'Pants'],
                    ['value' => 'mens-shoes', 'label' => 'Shoes'],
                ]
            ],
            [
                'value' => 'womens',
                'label' => "Women's Clothing",
                'children' => [
                    ['value' => 'womens-dresses', 'label' => 'Dresses'],
                    ['value' => 'womens-tops', 'label' => 'Tops'],
                    ['value' => 'womens-shoes', 'label' => 'Shoes'],
                ]
            ]
        ]
    ],
    [
        'value' => 'books',
        'label' => 'Books',
        'children' => [
            ['value' => 'fiction', 'label' => 'Fiction'],
            ['value' => 'nonfiction', 'label' => 'Non-Fiction'],
            ['value' => 'textbooks', 'label' => 'Textbooks'],
        ]
    ]
];

$form2 = FormBuilder::create('categories-independent')
    ->setAction('/save-categories')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Product Categories', 'Select categories independently. Each checkbox can be checked/unchecked without affecting others.')
    
    ->addCheckboxTree(
        'categories',
        'Available Categories',
        $categoriesTree,
        CheckboxTreeManager::MODE_INDEPENDENT
    )
    ->helpText('In independent mode, each checkbox is independent. Parent and children selections do not affect each other.')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Save Categories')
    ->build();

// Example 3: Pre-checked Items
$featuresTree = [
    [
        'value' => 'basic',
        'label' => 'Basic Features',
        'checked' => true,
        'children' => [
            ['value' => 'dashboard', 'label' => 'Dashboard', 'checked' => true],
            ['value' => 'profile', 'label' => 'User Profile', 'checked' => true],
            ['value' => 'notifications', 'label' => 'Notifications', 'checked' => false],
        ]
    ],
    [
        'value' => 'advanced',
        'label' => 'Advanced Features',
        'checked' => false,
        'children' => [
            ['value' => 'analytics', 'label' => 'Analytics Dashboard', 'checked' => false],
            ['value' => 'api', 'label' => 'API Access', 'checked' => false],
            ['value' => 'webhooks', 'label' => 'Webhooks', 'checked' => false],
        ]
    ],
    [
        'value' => 'premium',
        'label' => 'Premium Features',
        'disabled' => true,
        'children' => [
            ['value' => 'ai', 'label' => 'AI Assistant', 'disabled' => true],
            ['value' => 'priority', 'label' => 'Priority Support', 'disabled' => true],
        ]
    ]
];

$form3 = FormBuilder::create('features-prechecked')
    ->setAction('/save-features')
    ->setMethod('POST')
    ->setTheme(new Bootstrap5Theme())
    
    ->addSection('Plan Features', 'Pre-checked and disabled options')
    
    ->addCheckboxTree(
        'features',
        'Select Features',
        $featuresTree,
        CheckboxTreeManager::MODE_CASCADE
    )
    ->helpText('Some features are pre-selected (Basic package). Premium features are disabled (upgrade required).')
    ->add()
    
    ->endSection()
    
    ->addSubmit('Update Plan')
    ->build();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkbox Tree Examples</title>
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
            <h1 class="display-4">Checkbox Tree</h1>
            <p class="lead text-muted">Hierarchical checkboxes with cascade and independent modes</p>
        </div>

        <!-- Example 1 -->
        <div class="example-section">
            <h2 class="example-title">Example 1: Cascade Mode (Parent→Children)</h2>
            <div class="feature-badge">Cascade Mode</div>
            <div class="feature-badge">Indeterminate States</div>
            <div class="feature-badge">Auto-sync</div>
            
            <div class="alert alert-info">
                <strong>How it works:</strong>
                <ul class="mb-0">
                    <li>Check a parent → All children are automatically checked</li>
                    <li>Uncheck a parent → All children are automatically unchecked</li>
                    <li>Check some children → Parent shows as "indeterminate" (dash icon)</li>
                    <li>Check all children → Parent automatically becomes checked</li>
                </ul>
            </div>
            
            <?= $form1 ?>
        </div>

        <!-- Example 2 -->
        <div class="example-section">
            <h2 class="example-title">Example 2: Independent Mode</h2>
            <div class="feature-badge">Independent</div>
            <div class="feature-badge">No Cascade</div>
            <div class="feature-badge">Free Selection</div>
            
            <div class="alert alert-info">
                <strong>How it works:</strong>
                <ul class="mb-0">
                    <li>Each checkbox is completely independent</li>
                    <li>Checking a parent does NOT affect children</li>
                    <li>Checking children does NOT affect parent</li>
                    <li>Useful for hierarchical display without cascade behavior</li>
                </ul>
            </div>
            
            <?= $form2 ?>
        </div>

        <!-- Example 3 -->
        <div class="example-section">
            <h2 class="example-title">Example 3: Pre-checked & Disabled</h2>
            <div class="feature-badge">Default Values</div>
            <div class="feature-badge">Disabled Items</div>
            <div class="feature-badge">Mixed States</div>
            
            <div class="alert alert-info">
                <strong>Features:</strong>
                <ul class="mb-0">
                    <li>Items can be pre-checked using <code>checked: true</code></li>
                    <li>Items can be disabled using <code>disabled: true</code></li>
                    <li>Perfect for showing included/locked features in different plan tiers</li>
                </ul>
            </div>
            
            <?= $form3 ?>
        </div>

        <div class="alert alert-success">
            <h5>JavaScript API</h5>
            <p>Each checkbox tree exposes a global API for programmatic control:</p>
            <pre class="mb-0"><code>// Get checked values
const values = CheckboxTree_permissions_cascade.getCheckedValues();
console.log(values); // ['users', 'users.view', ...]

// Set checked values programmatically
CheckboxTree_permissions_cascade.setCheckedValues(['users', 'users.view', 'users.create']);

// Listen to events
document.querySelector('[data-checkbox-tree="permissions"]')
    .addEventListener('change', (e) => {
        console.log('Checkbox changed:', e.target.value);
    });</code></pre>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
