<?php

declare(strict_types=1);

namespace SpiderForm\V2\Integration\Laravel;

use SpiderForm\V2\Builder\FormBuilder;
use SpiderForm\V2\Renderer\TwigRenderer;
use SpiderForm\V2\Security\SecurityManager;
use SpiderForm\V2\Theme\Bootstrap5Theme;
use Illuminate\Support\ServiceProvider;

/**
 * Form Generator Laravel Service Provider
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/spider-form.php',
            'spider-form'
        );

        // Register security manager
        $this->app->singleton(SecurityManager::class, function ($app) {
            return new SecurityManager(
                useSession: true,
                hashAlgo: config('spider-form.security.hash_algo', 'sha256')
            );
        });

        // Register default renderer
        $this->app->singleton(TwigRenderer::class, function ($app) {
            $templatePaths = config('spider-form.template_paths', [
                __DIR__ . '/../../Theme/templates',
            ]);

            $cacheDir = config('spider-form.cache.enabled', true)
                ? storage_path('framework/cache/spider-form')
                : null;

            return new TwigRenderer(
                templatePaths: $templatePaths,
                cacheDir: $cacheDir,
                debug: config('app.debug', false)
            );
        });

        // Register default theme
        $this->app->singleton(Bootstrap5Theme::class, function ($app) {
            return new Bootstrap5Theme(
                config('spider-form.theme.config', [])
            );
        });

        // Register FormBuilder factory
        $this->app->bind(FormBuilder::class, function ($app) {
            // This will create a new instance each time
            return FormBuilder::create('form_' . uniqid());
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/spider-form.php' => config_path('spider-form.php'),
        ], 'spider-form-config');

        // Publish templates
        $this->publishes([
            __DIR__ . '/../../Theme/templates' => resource_path('views/vendor/spider-form'),
        ], 'spider-form-templates');

        // Register Blade directive
        $this->registerBladeDirectives();

        // Register view composer
        $this->registerViewComposer();
    }

    /**
     * Register Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        if (!$this->app->has('blade.compiler')) {
            return;
        }

        $blade = $this->app->get('blade.compiler');

        // @formGenerator directive
        $blade->directive('formGenerator', function ($expression) {
            return "<?php echo {$expression}->build(); ?>";
        });

        // @formAssets directive for including theme CSS/JS
        $blade->directive('formAssets', function ($expression) {
            return "<?php
                \$theme = {$expression};
                \$assets = \$theme->getAssets();
                foreach (\$assets['css'] as \$css) {
                    echo '<link rel=\"stylesheet\" href=\"' . \$css . '\">';
                }
                foreach (\$assets['js'] as \$js) {
                    echo '<script src=\"' . \$js . '\"></script>';
                }
            ?>";
        });
    }

    /**
     * Register view composer
     */
    protected function registerViewComposer(): void
    {
        if (!$this->app->has('view')) {
            return;
        }

        $this->app->get('view')->composer('*', function ($view) {
            $view->with('formGeneratorVersion', '2.0.0');
        });
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            SecurityManager::class,
            TwigRenderer::class,
            Bootstrap5Theme::class,
            FormBuilder::class,
        ];
    }
}
