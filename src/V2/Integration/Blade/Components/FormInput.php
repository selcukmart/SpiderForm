<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Blade\Components;

use Illuminate\View\Component;
use FormGenerator\V2\Integration\Blade\FormGeneratorBladeDirectives;

/**
 * Base Form Input Component
 *
 * @author selcukmart
 * @since 2.2.0
 */
abstract class FormInput extends Component
{
    public string $name;
    public ?string $label;
    public bool $required;
    public ?string $placeholder;
    public ?string $help;
    public mixed $value;
    public ?string $class;

    public function __construct(
        string $name,
        ?string $label = null,
        bool $required = false,
        ?string $placeholder = null,
        ?string $help = null,
        mixed $value = null,
        ?string $class = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->placeholder = $placeholder;
        $this->help = $help;
        $this->value = $value;
        $this->class = $class;
    }

    protected function getOptions(): array
    {
        return array_filter([
            'required' => $this->required,
            'placeholder' => $this->placeholder,
            'help' => $this->help,
            'value' => $this->value,
            'class' => $this->class,
        ], fn($v) => $v !== null && $v !== false);
    }
}

/**
 * Text Input Component
 *
 * Usage:
 * ```blade
 * <x-form-text name="username" label="Username" required />
 * ```
 */
class FormText extends FormInput
{
    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormText(
            $this->name,
            $this->label,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Email Input Component
 *
 * Usage:
 * ```blade
 * <x-form-email name="email" label="Email" required />
 * ```
 */
class FormEmail extends FormInput
{
    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormEmail(
            $this->name,
            $this->label,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Password Input Component
 *
 * Usage:
 * ```blade
 * <x-form-password name="password" label="Password" required />
 * ```
 */
class FormPassword extends FormInput
{
    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormPassword(
            $this->name,
            $this->label,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Textarea Component
 *
 * Usage:
 * ```blade
 * <x-form-textarea name="description" label="Description" rows="5" />
 * ```
 */
class FormTextarea extends FormInput
{
    public int $rows;
    public ?int $cols;

    public function __construct(
        string $name,
        ?string $label = null,
        bool $required = false,
        ?string $placeholder = null,
        ?string $help = null,
        mixed $value = null,
        ?string $class = null,
        int $rows = 5,
        ?int $cols = null
    ) {
        parent::__construct($name, $label, $required, $placeholder, $help, $value, $class);
        $this->rows = $rows;
        $this->cols = $cols;
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $options['rows'] = $this->rows;
        if ($this->cols !== null) {
            $options['cols'] = $this->cols;
        }
        return $options;
    }

    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormTextarea(
            $this->name,
            $this->label,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Number Input Component
 *
 * Usage:
 * ```blade
 * <x-form-number name="age" label="Age" min="18" max="100" />
 * ```
 */
class FormNumber extends FormInput
{
    public mixed $min;
    public mixed $max;

    public function __construct(
        string $name,
        ?string $label = null,
        bool $required = false,
        ?string $placeholder = null,
        ?string $help = null,
        mixed $value = null,
        ?string $class = null,
        mixed $min = null,
        mixed $max = null
    ) {
        parent::__construct($name, $label, $required, $placeholder, $help, $value, $class);
        $this->min = $min;
        $this->max = $max;
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        if ($this->min !== null) {
            $options['min'] = $this->min;
        }
        if ($this->max !== null) {
            $options['max'] = $this->max;
        }
        return $options;
    }

    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormNumber(
            $this->name,
            $this->label,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Select Component
 *
 * Usage:
 * ```blade
 * <x-form-select name="country" label="Country" :options="$countries" />
 * ```
 */
class FormSelect extends FormInput
{
    public array $selectOptions;

    public function __construct(
        string $name,
        ?string $label = null,
        array $selectOptions = [],
        bool $required = false,
        ?string $help = null,
        mixed $value = null,
        ?string $class = null
    ) {
        parent::__construct($name, $label, $required, null, $help, $value, $class);
        $this->selectOptions = $selectOptions;
    }

    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormSelect(
            $this->name,
            $this->label,
            $this->selectOptions,
            $this->getOptions()
        );
        return '';
    }
}

/**
 * Submit Button Component
 *
 * Usage:
 * ```blade
 * <x-form-submit>Save</x-form-submit>
 * ```
 */
class FormSubmit extends Component
{
    public string $label;

    public function __construct(string $label = 'Submit')
    {
        $this->label = $label;
    }

    public function render()
    {
        FormGeneratorBladeDirectives::directiveFormSubmit($this->label);
        return '';
    }
}
