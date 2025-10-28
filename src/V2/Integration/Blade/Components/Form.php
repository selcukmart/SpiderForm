<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Blade\Components;

use Illuminate\View\Component;
use FormGenerator\V2\Integration\Blade\FormGeneratorBladeDirectives;

/**
 * Form Blade Component
 *
 * Usage:
 * ```blade
 * <x-form name="user-form" action="/submit" method="POST">
 *     <!-- Form inputs here -->
 * </x-form>
 * ```
 *
 * @author selcukmart
 * @since 2.2.0
 */
class Form extends Component
{
    public string $name;
    public ?string $action;
    public string $method;
    public array $attributes;

    public function __construct(
        string $name,
        ?string $action = null,
        string $method = 'POST',
        array $attributes = []
    ) {
        $this->name = $name;
        $this->action = $action;
        $this->method = $method;
        $this->attributes = $attributes;
    }

    public function render()
    {
        $options = array_filter([
            'action' => $this->action,
            'method' => $this->method,
        ]) + $this->attributes;

        FormGeneratorBladeDirectives::directiveFormStart($this->name, $options);

        return function (array $data) {
            // Slot content is rendered here
            return FormGeneratorBladeDirectives::directiveFormEnd();
        };
    }
}
