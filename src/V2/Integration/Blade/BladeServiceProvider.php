<?php

declare(strict_types=1);

namespace SpiderForm\V2\Integration\Blade;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use SpiderForm\V2\Integration\Blade\Components\{
    Form,
    FormText,
    FormEmail,
    FormPassword,
    FormTextarea,
    FormNumber,
    FormSelect,
    FormSubmit
};
use SpiderForm\V2\Renderer\BladeRenderer;
use SpiderForm\V2\Theme\Bootstrap5Theme;

/**
 * Laravel Service Provider for SpiderForm Blade Integration
 *
 * Automatically registers:
 * - Blade directives (@formStart, @formText, @formEnd, etc.)
 * - Blade components (<x-form>, <x-form-text>, etc.)
 * - BladeRenderer in the service container
 *
 * Installation:
 * Add to config/app.php providers array:
 * ```php
 * 'providers' => [
 *     // Other providers...
 *     \SpiderForm\V2\Integration\Blade\BladeServiceProvider::class,
 * ],
 * ```
 *
 * Or for Laravel 11+ (auto-discovery enabled by default in composer.json)
 *
 * @author selcukmart
 * @since 2.2.0
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register BladeRenderer as a singleton
        $this->app->singleton(BladeRenderer::class, function ($app) {
            $viewPaths = $app['config']->get('view.paths', [resource_path('views')]);
            $cachePath = $app['config']->get('view.compiled', storage_path('framework/views'));

            return new BladeRenderer($viewPaths, $cachePath);
        });

        // Bind RendererInterface to BladeRenderer
        $this->app->bind(
            \SpiderForm\V2\Contracts\RendererInterface::class,
            BladeRenderer::class
        );

        // Register FormBuilder in container
        $this->app->singleton(\SpiderForm\V2\Builder\FormBuilder::class, function ($app) {
            $renderer = $app->make(BladeRenderer::class);
            $theme = new Bootstrap5Theme();

            FormGeneratorBladeDirectives::setRenderer($renderer);
            FormGeneratorBladeDirectives::setDefaultTheme($theme);

            return \SpiderForm\V2\Builder\FormBuilder::create('default')
                ->setRenderer($renderer)
                ->setTheme($theme);
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Set up Blade directives and components dependencies
        $renderer = $this->app->make(BladeRenderer::class);
        $theme = new Bootstrap5Theme();

        FormGeneratorBladeDirectives::setRenderer($renderer);
        FormGeneratorBladeDirectives::setDefaultTheme($theme);

        // Register Blade directives
        $this->registerBladeDirectives();

        // Register Blade components
        $this->registerBladeComponents();

        // Publish configuration if needed
        $this->publishes([
            __DIR__ . '/../Laravel/config/spider-form.php' => config_path('spider-form.php'),
        ], 'spider-form-config');

        // Publish views if needed
        if (is_dir(__DIR__ . '/views')) {
            $this->publishes([
                __DIR__ . '/views' => resource_path('views/vendor/spider-form'),
            ], 'spider-form-views');
        }
    }

    /**
     * Register Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        FormGeneratorBladeDirectives::register();
    }

    /**
     * Register Blade components
     */
    protected function registerBladeComponents(): void
    {
        // Register form component
        Blade::component('form', Form::class);

        // Register input components
        Blade::component('form-text', FormText::class);
        Blade::component('form-email', FormEmail::class);
        Blade::component('form-password', FormPassword::class);
        Blade::component('form-textarea', FormTextarea::class);
        Blade::component('form-number', FormNumber::class);
        Blade::component('form-select', FormSelect::class);
        Blade::component('form-submit', FormSubmit::class);
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            BladeRenderer::class,
            \SpiderForm\V2\Contracts\RendererInterface::class,
            \SpiderForm\V2\Builder\FormBuilder::class,
        ];
    }
}
