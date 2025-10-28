<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Blade;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Contracts\ThemeInterface;
use FormGenerator\V2\Contracts\RendererInterface;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use Illuminate\Support\Facades\Blade;

/**
 * Blade Directives for FormGenerator V2
 *
 * Provides custom Blade directives for easy form generation in Laravel Blade templates.
 *
 * Usage in Blade templates:
 * ```blade
 * @formStart('user-form', ['action' => '/submit', 'method' => 'POST'])
 * @formText('username', 'Username', ['required' => true])
 * @formEmail('email', 'Email')
 * @formPassword('password', 'Password', ['required' => true])
 * @formSubmit('Save')
 * @formEnd
 * ```
 *
 * Or using components:
 * ```blade
 * <x-form name="user-form" action="/submit" method="POST">
 *     <x-form-text name="username" label="Username" required />
 *     <x-form-email name="email" label="Email" />
 *     <x-form-submit>Save</x-form-submit>
 * </x-form>
 * ```
 *
 * @author selcukmart
 * @since 2.2.0
 */
class FormGeneratorBladeDirectives
{
    private static ?FormBuilder $currentForm = null;
    private static ?ThemeInterface $defaultTheme = null;
    private static ?RendererInterface $renderer = null;

    /**
     * Set default theme
     */
    public static function setDefaultTheme(ThemeInterface $theme): void
    {
        self::$defaultTheme = $theme;
    }

    /**
     * Set renderer
     */
    public static function setRenderer(RendererInterface $renderer): void
    {
        self::$renderer = $renderer;
    }

    /**
     * Get current form builder instance
     */
    public static function getCurrentForm(): ?FormBuilder
    {
        return self::$currentForm;
    }

    /**
     * Register all Blade directives
     */
    public static function register(): void
    {
        // Form control directives
        Blade::directive('formStart', function ($expression) {
            return "<?php echo \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormStart($expression); ?>";
        });

        Blade::directive('formEnd', function () {
            return "<?php echo \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormEnd(); ?>";
        });

        // Input directives
        Blade::directive('formText', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormText($expression); ?>";
        });

        Blade::directive('formEmail', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormEmail($expression); ?>";
        });

        Blade::directive('formPassword', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormPassword($expression); ?>";
        });

        Blade::directive('formTextarea', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormTextarea($expression); ?>";
        });

        Blade::directive('formNumber', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormNumber($expression); ?>";
        });

        Blade::directive('formDate', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormDate($expression); ?>";
        });

        Blade::directive('formSelect', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormSelect($expression); ?>";
        });

        Blade::directive('formCheckbox', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormCheckbox($expression); ?>";
        });

        Blade::directive('formRadio', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormRadio($expression); ?>";
        });

        Blade::directive('formSubmit', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormSubmit($expression); ?>";
        });

        // Laravel-specific directives
        Blade::directive('formButton', function ($expression) {
            return "<?php \\FormGenerator\\V2\\Integration\\Blade\\FormGeneratorBladeDirectives::directiveFormButton($expression); ?>";
        });
    }

    /**
     * @formStart directive implementation
     */
    public static function directiveFormStart(string $name, array $options = []): string
    {
        if (self::$renderer === null) {
            throw new \RuntimeException('Renderer not set. Call FormGeneratorBladeDirectives::setRenderer() first.');
        }

        $theme = self::$defaultTheme ?? new Bootstrap5Theme();

        self::$currentForm = FormBuilder::create($name)
            ->setRenderer(self::$renderer)
            ->setTheme($theme);

        if (isset($options['action'])) {
            self::$currentForm->setAction($options['action']);
        }

        if (isset($options['method'])) {
            self::$currentForm->setMethod($options['method']);
        }

        if (isset($options['class'])) {
            self::$currentForm->attributes(['class' => $options['class']]);
        }

        return '';
    }

    /**
     * @formEnd directive implementation
     */
    public static function directiveFormEnd(): string
    {
        if (self::$currentForm === null) {
            throw new \RuntimeException('No form started. Use @formStart first.');
        }

        $html = self::$currentForm->build();
        self::$currentForm = null;

        return $html;
    }

    /**
     * @formText directive implementation
     */
    public static function directiveFormText(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addText($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formEmail directive implementation
     */
    public static function directiveFormEmail(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addEmail($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formPassword directive implementation
     */
    public static function directiveFormPassword(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addPassword($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formTextarea directive implementation
     */
    public static function directiveFormTextarea(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addTextarea($name, $label);

        if (isset($options['rows'])) {
            $input->rows((int) $options['rows']);
        }

        if (isset($options['cols'])) {
            $input->cols((int) $options['cols']);
        }

        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formNumber directive implementation
     */
    public static function directiveFormNumber(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addNumber($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formDate directive implementation
     */
    public static function directiveFormDate(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addDate($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formSelect directive implementation
     */
    public static function directiveFormSelect(string $name, ?string $label = null, array $selectOptions = [], array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addSelect($name, $label);

        if (!empty($selectOptions)) {
            $input->options($selectOptions);
        }

        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formCheckbox directive implementation
     */
    public static function directiveFormCheckbox(string $name, ?string $label = null, array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addCheckbox($name, $label);
        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formRadio directive implementation
     */
    public static function directiveFormRadio(string $name, ?string $label = null, array $radioOptions = [], array $options = []): void
    {
        self::checkForm();

        $input = self::$currentForm->addRadio($name, $label);

        if (!empty($radioOptions)) {
            $input->options($radioOptions);
        }

        self::applyOptions($input, $options);
        $input->add();
    }

    /**
     * @formSubmit directive implementation
     */
    public static function directiveFormSubmit(string $label = 'Submit', array $options = []): void
    {
        self::checkForm();

        self::$currentForm->addSubmit('submit', $label);
    }

    /**
     * @formButton directive implementation
     */
    public static function directiveFormButton(string $label, string $type = 'button', array $options = []): void
    {
        self::checkForm();

        $button = self::$currentForm->addButton($label, $type);

        if (isset($options['class'])) {
            $button->attribute('class', $options['class']);
        }

        if (isset($options['onclick'])) {
            $button->attribute('onclick', $options['onclick']);
        }

        $button->add();
    }

    /**
     * Check if form is started
     */
    private static function checkForm(): void
    {
        if (self::$currentForm === null) {
            throw new \RuntimeException('No form started. Use @formStart first.');
        }
    }

    /**
     * Apply common options to input
     */
    private static function applyOptions($input, array $options): void
    {
        if (isset($options['required']) && $options['required']) {
            $input->required();
        }

        if (isset($options['placeholder'])) {
            $input->placeholder($options['placeholder']);
        }

        if (isset($options['help'])) {
            $input->helpText($options['help']);
        }

        if (isset($options['value'])) {
            $input->value($options['value']);
        }

        if (isset($options['class'])) {
            $input->attribute('class', $options['class']);
        }

        if (isset($options['min'])) {
            $input->min($options['min']);
        }

        if (isset($options['max'])) {
            $input->max($options['max']);
        }

        if (isset($options['minLength'])) {
            $input->minLength((int) $options['minLength']);
        }

        if (isset($options['maxLength'])) {
            $input->maxLength((int) $options['maxLength']);
        }

        if (isset($options['pattern'])) {
            $input->pattern($options['pattern']);
        }

        if (isset($options['readonly']) && $options['readonly']) {
            $input->readonly();
        }

        if (isset($options['disabled']) && $options['disabled']) {
            $input->disabled();
        }
    }
}
