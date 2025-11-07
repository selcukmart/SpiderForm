<?php

declare(strict_types=1);

namespace SpiderForm\V2\Theme;

use SpiderForm\V2\Contracts\InputType;

/**
 * Bootstrap 3 Theme
 *
 * @author selcukmart
 * @since 2.0.0
 */
class Bootstrap3Theme extends AbstractTheme
{
    protected function initialize(): void
    {
        // Set template mappings
        $this->templateMap = [
            'text' => 'bootstrap3/input_text.tpl',
            'email' => 'bootstrap3/input_text.tpl',
            'password' => 'bootstrap3/input_text.tpl',
            'number' => 'bootstrap3/input_text.tpl',
            'tel' => 'bootstrap3/input_text.tpl',
            'url' => 'bootstrap3/input_text.tpl',
            'search' => 'bootstrap3/input_text.tpl',
            'date' => 'bootstrap3/input_text.tpl',
            'time' => 'bootstrap3/input_text.tpl',
            'datetime-local' => 'bootstrap3/input_text.tpl',
            'month' => 'bootstrap3/input_text.tpl',
            'week' => 'bootstrap3/input_text.tpl',
            'color' => 'bootstrap3/input_text.tpl',
            'range' => 'bootstrap3/input_range.tpl',
            'textarea' => 'bootstrap3/input_textarea.tpl',
            'select' => 'bootstrap3/input_select.tpl',
            'checkbox' => 'bootstrap3/input_checkbox.tpl',
            'radio' => 'bootstrap3/input_radio.tpl',
            'file' => 'bootstrap3/input_file.tpl',
            'hidden' => 'bootstrap3/input_hidden.tpl',
            'submit' => 'bootstrap3/button.tpl',
            'reset' => 'bootstrap3/button.tpl',
            'button' => 'bootstrap3/button.tpl',
            'checkbox_tree' => 'bootstrap3/input_checkbox_tree.tpl',
            'repeater' => 'bootstrap3/input_repeater.tpl',
            'default' => 'bootstrap3/input_text.tpl',
        ];

        // Set input CSS classes
        $this->inputClasses = [
            'text' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'email' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'password' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'textarea' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'select' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'checkbox' => [
                'wrapper' => 'checkbox',
                'label' => '',
                'input' => '',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'radio' => [
                'wrapper' => 'radio',
                'label' => '',
                'input' => '',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'file' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'range' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => '',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'submit' => [
                'button' => 'btn btn-primary',
            ],
            'reset' => [
                'button' => 'btn btn-default',
            ],
            'button' => [
                'button' => 'btn btn-default',
            ],
            'checkbox_tree' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
            'repeater' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'help' => 'help-block',
                'error' => 'help-block',
                'item' => 'panel panel-default',
                'item-header' => 'panel-heading clearfix',
                'item-body' => 'panel-body',
                'add-button' => 'btn btn-sm btn-success',
                'remove-button' => 'btn btn-sm btn-danger',
            ],
            'default' => [
                'wrapper' => 'form-group',
                'label' => 'control-label',
                'input' => 'form-control',
                'help' => 'help-block',
                'error' => 'help-block',
            ],
        ];

        // Copy common input types
        $commonTypes = ['number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local', 'month', 'week', 'color'];
        foreach ($commonTypes as $type) {
            $this->inputClasses[$type] = $this->inputClasses['text'];
        }

        // Set form CSS classes
        $this->formClasses = [
            'form' => '',
            'row' => 'row',
            'col' => 'col',
        ];

        // Set assets
        $this->assets = [
            'css' => [
                'https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css',
            ],
            'js' => [
                'https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js',
            ],
        ];
    }

    protected function getDefaultConfig(): array
    {
        return [
            'form_template' => 'bootstrap3/form.tpl',
            'input_capsule_template' => 'bootstrap3/input_capsule.tpl',
            'inline_forms' => false,
            'horizontal_forms' => false,
            'validation_feedback' => true,
        ];
    }

    public function getName(): string
    {
        return 'Bootstrap 3';
    }

    public function getVersion(): string
    {
        return '3.4.1';
    }

    /**
     * Enable inline form style
     */
    public function enableInlineForm(): self
    {
        $this->config['inline_forms'] = true;
        $this->formClasses['form'] = 'form-inline';
        return $this;
    }

    /**
     * Enable horizontal form style
     */
    public function enableHorizontalForm(string $labelCol = 'col-sm-2', string $inputCol = 'col-sm-10'): self
    {
        $this->config['horizontal_forms'] = true;
        $this->config['label_col_class'] = $labelCol;
        $this->config['input_col_class'] = $inputCol;
        $this->formClasses['form'] = 'form-horizontal';
        return $this;
    }
}
