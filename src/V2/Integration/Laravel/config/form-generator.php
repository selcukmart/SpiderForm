<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | The default theme to use when rendering forms.
    | Available: 'bootstrap5', 'tailwind', 'generic'
    |
    */
    'default_theme' => env('FORM_GENERATOR_THEME', 'bootstrap5'),

    /*
    |--------------------------------------------------------------------------
    | Default Renderer
    |--------------------------------------------------------------------------
    |
    | The default template renderer to use.
    | Available: 'twig', 'smarty', 'blade'
    |
    */
    'default_renderer' => env('FORM_GENERATOR_RENDERER', 'twig'),

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | Additional template paths to search for form templates.
    |
    */
    'template_paths' => [
        resource_path('views/vendor/form-generator'),
        __DIR__ . '/../../../Theme/templates',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure template caching for better performance.
    |
    */
    'cache' => [
        'enabled' => env('FORM_GENERATOR_CACHE', true),
        'dir' => storage_path('framework/cache/form-generator'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security features like CSRF protection.
    |
    */
    'security' => [
        'csrf_enabled' => true,
        'hash_algo' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Theme-specific configuration options.
    |
    */
    'theme' => [
        'config' => [
            'floating_labels' => false,
            'inline_forms' => false,
            'horizontal_forms' => false,
        ],
    ],
];
