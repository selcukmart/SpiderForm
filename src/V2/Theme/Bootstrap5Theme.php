<?php

declare(strict_types=1);

namespace FormGenerator\V2\Theme;

use FormGenerator\V2\Contracts\InputType;

/**
 * Bootstrap 5 Theme
 *
 * @author selcukmart
 * @since 2.0.0
 */
class Bootstrap5Theme extends AbstractTheme
{
    protected function initialize(): void
    {
        // Set template mappings
        $this->templateMap = [
            'text' => 'bootstrap5/input_text.twig',
            'email' => 'bootstrap5/input_text.twig',
            'password' => 'bootstrap5/input_text.twig',
            'number' => 'bootstrap5/input_text.twig',
            'tel' => 'bootstrap5/input_text.twig',
            'url' => 'bootstrap5/input_text.twig',
            'search' => 'bootstrap5/input_text.twig',
            'date' => 'bootstrap5/input_text.twig',
            'time' => 'bootstrap5/input_text.twig',
            'datetime-local' => 'bootstrap5/input_text.twig',
            'month' => 'bootstrap5/input_text.twig',
            'week' => 'bootstrap5/input_text.twig',
            'color' => 'bootstrap5/input_text.twig',
            'range' => 'bootstrap5/input_range.twig',
            'textarea' => 'bootstrap5/input_textarea.twig',
            'select' => 'bootstrap5/input_select.twig',
            'checkbox' => 'bootstrap5/input_checkbox.twig',
            'radio' => 'bootstrap5/input_radio.twig',
            'file' => 'bootstrap5/input_file.twig',
            'hidden' => 'bootstrap5/input_hidden.twig',
            'submit' => 'bootstrap5/button.twig',
            'reset' => 'bootstrap5/button.twig',
            'button' => 'bootstrap5/button.twig',
            'checkbox_tree' => 'bootstrap5/input_checkbox_tree.twig',
            'repeater' => 'bootstrap5/input_repeater.twig',
            'default' => 'bootstrap5/input_text.twig',
        ];

        // Set input CSS classes
        $this->inputClasses = [
            'text' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'email' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'password' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'textarea' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'select' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-select',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'checkbox' => [
                'wrapper' => 'mb-3 form-check',
                'label' => 'form-check-label',
                'input' => 'form-check-input',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'radio' => [
                'wrapper' => 'mb-3 form-check',
                'label' => 'form-check-label',
                'input' => 'form-check-input',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'file' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'range' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-range',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'submit' => [
                'button' => 'btn btn-primary',
            ],
            'reset' => [
                'button' => 'btn btn-secondary',
            ],
            'button' => [
                'button' => 'btn btn-outline-primary',
            ],
            'checkbox_tree' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label fw-bold',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
            ],
            'repeater' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label fw-bold',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
                'item' => 'card mb-2',
                'item-header' => 'card-header bg-light d-flex justify-content-between align-items-center',
                'item-body' => 'card-body',
                'add-button' => 'btn btn-sm btn-success',
                'remove-button' => 'btn btn-sm btn-danger',
            ],
            'default' => [
                'wrapper' => 'mb-3',
                'label' => 'form-label',
                'input' => 'form-control',
                'help' => 'form-text text-muted',
                'error' => 'invalid-feedback',
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
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            ],
            'js' => [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            ],
        ];
    }

    protected function getDefaultConfig(): array
    {
        return [
            'form_template' => 'bootstrap5/form.twig',
            'input_capsule_template' => 'bootstrap5/input_capsule.twig',
            'floating_labels' => false,
            'inline_forms' => false,
            'horizontal_forms' => false,
            'validation_feedback' => true,
        ];
    }

    public function getName(): string
    {
        return 'Bootstrap 5';
    }

    public function getVersion(): string
    {
        return '5.3.0';
    }

    /**
     * Enable floating labels style
     */
    public function enableFloatingLabels(): self
    {
        $this->config['floating_labels'] = true;
        return $this;
    }

    /**
     * Enable inline form style
     */
    public function enableInlineForm(): self
    {
        $this->config['inline_forms'] = true;
        $this->formClasses['form'] = 'row row-cols-lg-auto g-3 align-items-center';
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
        return $this;
    }
}
