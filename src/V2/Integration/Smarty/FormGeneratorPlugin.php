<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Smarty;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Contracts\ThemeInterface;
use FormGenerator\V2\Contracts\RendererInterface;
use FormGenerator\V2\Theme\Bootstrap5Theme;

/**
 * Smarty Plugin Helper for FormGenerator V2
 * 
 * Provides static methods to be used as Smarty function plugins.
 * 
 * Register plugins in your Smarty instance:
 * $smarty->registerPlugin('function', 'form_start', ['FormGeneratorPlugin', 'formStart']);
 * $smarty->registerPlugin('function', 'form_text', ['FormGeneratorPlugin', 'formText']);
 * $smarty->registerPlugin('function', 'form_end', ['FormGeneratorPlugin', 'formEnd']);
 * 
 * Usage in Smarty templates:
 * {form_start name="my-form" action="/submit" method="POST"}
 * {form_text name="username" label="Username" required=true}
 * {form_email name="email" label="Email"}
 * {form_submit label="Save"}
 * {form_end}
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorPlugin
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
     * Start form
     * {form_start name="my-form" action="/submit" method="POST"}
     */
    public static function formStart(array $params, $smarty): string
    {
        if (!isset($params['name'])) {
            throw new \InvalidArgumentException('name parameter is required');
        }

        if (self::$renderer === null) {
            throw new \RuntimeException('Renderer not set. Call FormGeneratorPlugin::setRenderer() first.');
        }

        $theme = self::$defaultTheme ?? new Bootstrap5Theme();

        self::$currentForm = FormBuilder::create($params['name'])
            ->setRenderer(self::$renderer)
            ->setTheme($theme);

        if (isset($params['action'])) {
            self::$currentForm->setAction($params['action']);
        }

        if (isset($params['method'])) {
            self::$currentForm->setMethod($params['method']);
        }

        return '';
    }

    /**
     * End and render form
     * {form_end}
     */
    public static function formEnd(array $params, $smarty): string
    {
        if (self::$currentForm === null) {
            throw new \RuntimeException('No form has been started');
        }

        $html = self::$currentForm->build();
        self::$currentForm = null;

        return $html;
    }

    /**
     * Add text input
     * {form_text name="username" label="Username" required=true placeholder="Enter username"}
     */
    public static function formText(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addText($name, $label);
        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add email input
     * {form_email name="email" label="Email" required=true}
     */
    public static function formEmail(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addEmail($name, $label);
        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add password input
     * {form_password name="password" label="Password" required=true}
     */
    public static function formPassword(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addPassword($name, $label);
        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add textarea
     * {form_textarea name="description" label="Description" rows=5}
     */
    public static function formTextarea(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addTextarea($name, $label);
        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add select dropdown
     * {form_select name="country" label="Country" options=$countryOptions}
     */
    public static function formSelect(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addSelect($name, $label);
        
        if (isset($params['options']) && is_array($params['options'])) {
            $input->options($params['options']);
        }

        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add checkbox
     * {form_checkbox name="terms" label="I agree to terms"}
     */
    public static function formCheckbox(array $params, $smarty): string
    {
        self::checkForm();

        $name = $params['name'] ?? throw new \InvalidArgumentException('name is required');
        $label = $params['label'] ?? null;

        $input = self::$currentForm->addCheckbox($name, $label);
        self::applyParams($input, $params);
        $input->add();

        return '';
    }

    /**
     * Add submit button
     * {form_submit label="Save"}
     */
    public static function formSubmit(array $params, $smarty): string
    {
        self::checkForm();

        $label = $params['label'] ?? 'Submit';
        self::$currentForm->addSubmit('submit', $label);

        return '';
    }

    /**
     * Check if form is started
     */
    private static function checkForm(): void
    {
        if (self::$currentForm === null) {
            throw new \RuntimeException('No form has been started. Use {form_start} first.');
        }
    }

    /**
     * Apply common parameters to input
     */
    private static function applyParams($input, array $params): void
    {
        if (isset($params['required']) && $params['required']) {
            $input->required();
        }

        if (isset($params['placeholder'])) {
            $input->placeholder($params['placeholder']);
        }

        if (isset($params['help'])) {
            $input->helpText($params['help']);
        }

        if (isset($params['value'])) {
            $input->value($params['value']);
        }

        if (isset($params['rows'])) {
            $input->rows((int)$params['rows']);
        }

        if (isset($params['cols'])) {
            $input->cols((int)$params['cols']);
        }

        if (isset($params['min'])) {
            $input->min($params['min']);
        }

        if (isset($params['max'])) {
            $input->max($params['max']);
        }
    }
}
