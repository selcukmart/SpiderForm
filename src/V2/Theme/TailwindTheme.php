<?php

declare(strict_types=1);

namespace FormGenerator\V2\Theme;

use FormGenerator\V2\Contracts\InputType;

/**
 * Tailwind CSS Theme
 *
 * @author selcukmart
 * @since 2.0.0
 */
class TailwindTheme extends AbstractTheme
{
    protected function initialize(): void
    {
        // Set template mappings (reuse Bootstrap5 templates with Tailwind classes)
        $this->templateMap = [
            'text' => 'tailwind/input_text.twig',
            'email' => 'tailwind/input_text.twig',
            'password' => 'tailwind/input_text.twig',
            'number' => 'tailwind/input_text.twig',
            'tel' => 'tailwind/input_text.twig',
            'url' => 'tailwind/input_text.twig',
            'search' => 'tailwind/input_text.twig',
            'date' => 'tailwind/input_text.twig',
            'time' => 'tailwind/input_text.twig',
            'datetime-local' => 'tailwind/input_text.twig',
            'month' => 'tailwind/input_text.twig',
            'week' => 'tailwind/input_text.twig',
            'color' => 'tailwind/input_text.twig',
            'range' => 'tailwind/input_range.twig',
            'textarea' => 'tailwind/input_textarea.twig',
            'select' => 'tailwind/input_select.twig',
            'checkbox' => 'tailwind/input_checkbox.twig',
            'radio' => 'tailwind/input_radio.twig',
            'file' => 'tailwind/input_file.twig',
            'hidden' => 'tailwind/input_hidden.twig',
            'submit' => 'tailwind/button.twig',
            'reset' => 'tailwind/button.twig',
            'button' => 'tailwind/button.twig',
            'checkbox_tree' => 'tailwind/input_checkbox_tree.twig',
            'repeater' => 'tailwind/input_repeater.twig',
            'default' => 'tailwind/input_text.twig',
        ];

        // Set Tailwind CSS classes
        $baseInputClasses = [
            'wrapper' => 'mb-4',
            'label' => 'block text-sm font-medium text-gray-700 mb-2',
            'input' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
            'help' => 'mt-2 text-sm text-gray-500',
            'error' => 'mt-2 text-sm text-red-600',
        ];

        $this->inputClasses = [
            'text' => $baseInputClasses,
            'email' => $baseInputClasses,
            'password' => $baseInputClasses,
            'number' => $baseInputClasses,
            'tel' => $baseInputClasses,
            'url' => $baseInputClasses,
            'search' => $baseInputClasses,
            'date' => $baseInputClasses,
            'time' => $baseInputClasses,
            'datetime-local' => $baseInputClasses,
            'month' => $baseInputClasses,
            'week' => $baseInputClasses,
            'color' => $baseInputClasses,
            'textarea' => $baseInputClasses,
            'select' => [
                'wrapper' => 'mb-4',
                'label' => 'block text-sm font-medium text-gray-700 mb-2',
                'input' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                'help' => 'mt-2 text-sm text-gray-500',
                'error' => 'mt-2 text-sm text-red-600',
            ],
            'checkbox' => [
                'wrapper' => 'mb-4 flex items-start',
                'label' => 'ml-3 text-sm font-medium text-gray-700',
                'input' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
                'help' => 'ml-7 mt-2 text-sm text-gray-500',
                'error' => 'ml-7 mt-2 text-sm text-red-600',
            ],
            'radio' => [
                'wrapper' => 'flex items-center mb-2',
                'label' => 'ml-3 text-sm font-medium text-gray-700',
                'input' => 'h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500',
                'help' => 'mt-2 text-sm text-gray-500',
                'error' => 'mt-2 text-sm text-red-600',
            ],
            'file' => $baseInputClasses,
            'range' => [
                'wrapper' => 'mb-4',
                'label' => 'block text-sm font-medium text-gray-700 mb-2',
                'input' => 'w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer',
                'help' => 'mt-2 text-sm text-gray-500',
                'error' => 'mt-2 text-sm text-red-600',
            ],
            'submit' => [
                'button' => 'inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
            ],
            'reset' => [
                'button' => 'inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
            ],
            'button' => [
                'button' => 'inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
            ],
            'checkbox_tree' => [
                'wrapper' => 'mb-4',
                'label' => 'block text-sm font-bold text-gray-900 mb-3',
                'help' => 'mt-2 text-sm text-gray-500',
                'error' => 'mt-2 text-sm text-red-600',
            ],
            'repeater' => [
                'wrapper' => 'mb-4',
                'label' => 'block text-sm font-bold text-gray-900 mb-3',
                'help' => 'mt-2 text-sm text-gray-500',
                'error' => 'mt-2 text-sm text-red-600',
                'item' => 'bg-white border border-gray-200 rounded-lg mb-3 shadow-sm',
                'item-header' => 'bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center rounded-t-lg',
                'item-body' => 'p-4',
                'add-button' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
                'remove-button' => 'inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
            ],
            'default' => $baseInputClasses,
        ];

        // Set form classes
        $this->formClasses = [
            'form' => 'space-y-6',
            'row' => 'grid grid-cols-1 gap-4 sm:grid-cols-2',
            'col' => '',
        ];

        // Set assets
        $this->assets = [
            'css' => [
                'https://cdn.tailwindcss.com',
            ],
            'js' => [],
        ];
    }

    protected function getDefaultConfig(): array
    {
        return [
            'form_template' => 'tailwind/form.twig',
            'input_capsule_template' => 'tailwind/input_capsule.twig',
            'color_scheme' => 'indigo', // indigo, blue, green, red, etc.
        ];
    }

    public function getName(): string
    {
        return 'Tailwind CSS';
    }

    public function getVersion(): string
    {
        return '3.x';
    }

    /**
     * Change color scheme
     *
     * @param string $color indigo, blue, green, red, purple, pink, etc.
     */
    public function setColorScheme(string $color): self
    {
        $this->config['color_scheme'] = $color;

        // Update button colors
        $this->inputClasses['submit']['button'] = str_replace('indigo', $color, $this->inputClasses['submit']['button']);

        return $this;
    }
}
