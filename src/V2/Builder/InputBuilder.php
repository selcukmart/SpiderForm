<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

use FormGenerator\V2\Contracts\InputType;
use FormGenerator\V2\Contracts\DataProviderInterface;
use FormGenerator\V2\Contracts\DataTransformerInterface;

/**
 * Input Builder - Chain Pattern for Individual Form Inputs
 *
 * @author selcukmart
 * @since 2.0.0
 */
class InputBuilder
{
    private string $name;
    private InputType $type;
    private mixed $value = null;
    private ?string $label = null;
    private ?string $placeholder = null;
    private ?string $helpText = null;
    private array $attributes = [];
    private array $wrapperAttributes = [];
    private array $labelAttributes = [];
    private array $validationRules = [];
    private array $options = [];
    private ?DataProviderInterface $optionsProvider = null;
    private array $dependencies = [];
    private bool $required = false;
    private bool $disabled = false;
    private bool $readonly = false;
    private ?string $defaultValue = null;
    private array $tree = [];
    private string $treeMode = CheckboxTreeManager::MODE_CASCADE;
    private ?FormBuilder $repeaterFields = null;
    private int $repeaterMin = 0;
    private int $repeaterMax = 10;
    private bool $pickerEnabled = true;  // Built-in picker enabled by default
    private array $pickerOptions = [];
    private array $fieldEventListeners = []; // Field-level event listeners
    private array $dataTransformers = []; // Data transformers (v2.3.1)

    public function __construct(
        private readonly FormBuilder $formBuilder,
        string $name,
        InputType $type
    ) {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Set input label
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set input placeholder
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Set help text below input
     */
    public function helpText(string $text): self
    {
        $this->helpText = $text;
        return $this;
    }

    /**
     * Set input value
     */
    public function value(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set default value (when no data provided)
     */
    public function defaultValue(mixed $value): self
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Mark input as required
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        if ($required) {
            $this->validationRules['required'] = true;
        }
        return $this;
    }

    /**
     * Mark input as disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Mark input as readonly
     */
    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * Set HTML attributes
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set single HTML attribute
     */
    public function attribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Set wrapper/container attributes
     */
    public function wrapperAttributes(array $attributes): self
    {
        $this->wrapperAttributes = array_merge($this->wrapperAttributes, $attributes);
        return $this;
    }

    /**
     * Set label attributes
     */
    public function labelAttributes(array $attributes): self
    {
        $this->labelAttributes = array_merge($this->labelAttributes, $attributes);
        return $this;
    }

    /**
     * Add CSS class
     */
    public function addClass(string $class): self
    {
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        $this->attributes['class'] = trim($this->attributes['class'] . ' ' . $class);
        return $this;
    }

    /**
     * Set options for select/radio/checkbox
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set options from data provider
     */
    public function optionsFromProvider(
        DataProviderInterface $provider,
        string $keyColumn,
        string $labelColumn,
        array $criteria = []
    ): self {
        $this->optionsProvider = $provider;
        $this->options = $provider->getOptions($keyColumn, $labelColumn, $criteria);
        return $this;
    }

    /**
     * Add dependency (show/hide based on another field)
     * This input will show/hide when the dependency field changes
     *
     * @param string $fieldName The field this input depends on
     * @param string|array $value The value(s) that trigger this input to show
     * @param string|null $group Optional group name for dependency management
     */
    public function dependsOn(string $fieldName, string|array $value, ?string $group = null): self
    {
        $values = is_array($value) ? $value : [$value];

        $this->dependencies[] = [
            'field' => $fieldName,
            'values' => $values,
            'group' => $group ?? $fieldName, // Use field name as default group
        ];

        // Add data attributes for JavaScript
        $dependValues = array_map(fn($v) => $fieldName . '-' . $v, $values);
        $this->wrapperAttributes['data-dependends'] = '';
        $this->wrapperAttributes['data-dependend'] = implode(' ', $dependValues);
        $this->wrapperAttributes['data-dependend-group'] = $group ?? $fieldName;

        // Initially hide dependent fields (will be shown by JS if condition met)
        $this->wrapperAttributes['style'] = 'display: none;';

        return $this;
    }

    /**
     * Add event listener for this field
     *
     * @param string $eventName Event name (use FieldEvents constants)
     * @param callable $listener Event listener callback
     * @param int $priority Priority (higher = earlier execution)
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): self
    {
        if (!isset($this->fieldEventListeners[$eventName])) {
            $this->fieldEventListeners[$eventName] = [];
        }

        $this->fieldEventListeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (highest first)
        usort($this->fieldEventListeners[$eventName], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        return $this;
    }

    /**
     * Shortcut: Add listener for when field value changes
     *
     * Example:
     * ```php
     * ->onValueChange(function(FieldEvent $event) {
     *     // Handle value change
     * })
     * ```
     */
    public function onValueChange(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.value_change', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when field is shown
     *
     * Example:
     * ```php
     * ->onShow(function(FieldEvent $event) {
     *     $event->getField()->required(true);
     * })
     * ```
     */
    public function onShow(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.show', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when field is hidden
     *
     * Example:
     * ```php
     * ->onHide(function(FieldEvent $event) {
     *     $event->getField()->required(false);
     * })
     * ```
     */
    public function onHide(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.hide', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when field is enabled
     */
    public function onEnable(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.enable', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when field is disabled
     */
    public function onDisable(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.disable', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for before field renders
     *
     * Example:
     * ```php
     * ->onPreRender(function(FieldEvent $event) {
     *     // Modify field config before render
     *     if ($event->getFieldValue('user_type') === 'admin') {
     *         $event->getField()->addClass('admin-only');
     *     }
     * })
     * ```
     */
    public function onPreRender(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.pre_render', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for after field renders
     */
    public function onPostRender(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.post_render', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for dependency check
     *
     * Example:
     * ```php
     * ->onDependencyCheck(function(FieldEvent $event) {
     *     $visible = $event->get('visible', true);
     *     // Custom logic to determine visibility
     *     if ($event->getFieldValue('custom_condition') === 'hide') {
     *         $visible = false;
     *     }
     *     $event->setVisible($visible);
     * })
     * ```
     */
    public function onDependencyCheck(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.dependency_check', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when dependency is met
     */
    public function onDependencyMet(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.dependency_met', $listener, $priority);
    }

    /**
     * Shortcut: Add listener for when dependency is not met
     */
    public function onDependencyNotMet(callable $listener, int $priority = 0): self
    {
        return $this->addEventListener('field.dependency_not_met', $listener, $priority);
    }

    /**
     * Get all field event listeners
     */
    public function getFieldEventListeners(): array
    {
        return $this->fieldEventListeners;
    }

    /**
     * Mark this input as a dependency controller
     * Other inputs can depend on this input's value
     *
     * @param string|null $group Optional group name
     */
    public function isDependency(?string $group = null): self
    {
        $this->attributes['data-dependency'] = 'true';
        $this->attributes['data-dependency-group'] = $group ?? $this->name;
        $this->attributes['data-dependency-field'] = $this->name;

        return $this;
    }

    /**
     * Shortcut: Make this input control dependencies and configure dependent
     *
     * Example:
     * ->controls('company_fields') // This field controls 'company_fields' group
     */
    public function controls(string $groupName): self
    {
        return $this->isDependency($groupName);
    }

    /**
     * Validation: Minimum length
     */
    public function minLength(int $length): self
    {
        $this->validationRules['minLength'] = $length;
        return $this;
    }

    /**
     * Validation: Maximum length
     */
    public function maxLength(int $length): self
    {
        $this->validationRules['maxLength'] = $length;
        $this->attributes['maxlength'] = $length;
        return $this;
    }

    /**
     * Validation: Minimum value (for number inputs)
     */
    public function min(int|float $value): self
    {
        $this->validationRules['min'] = $value;
        $this->attributes['min'] = $value;
        return $this;
    }

    /**
     * Validation: Maximum value (for number inputs)
     */
    public function max(int|float $value): self
    {
        $this->validationRules['max'] = $value;
        $this->attributes['max'] = $value;
        return $this;
    }

    /**
     * Validation: Pattern matching
     */
    public function pattern(string $regex, ?string $message = null): self
    {
        $this->validationRules['pattern'] = [
            'regex' => $regex,
            'message' => $message ?? "Invalid format for {$this->name}",
        ];
        $this->attributes['pattern'] = $regex;
        return $this;
    }

    /**
     * Validation: Email format
     */
    public function email(): self
    {
        $this->validationRules['email'] = true;
        $this->type = InputType::EMAIL;
        return $this;
    }

    /**
     * Validation: Set Laravel-style validation rules
     *
     * Example: ->rules('required|email|min:3|max:255')
     */
    public function rules(string $rules): self
    {
        $this->validationRules['rules'] = $rules;
        return $this;
    }

    /**
     * Validation: Value must be numeric
     */
    public function numeric(): self
    {
        $this->validationRules['numeric'] = true;
        return $this;
    }

    /**
     * Validation: Value must be an integer
     */
    public function integer(): self
    {
        $this->validationRules['integer'] = true;
        return $this;
    }

    /**
     * Validation: Value must be a string
     */
    public function string(): self
    {
        $this->validationRules['string'] = true;
        return $this;
    }

    /**
     * Validation: Value must be a boolean
     */
    public function boolean(): self
    {
        $this->validationRules['boolean'] = true;
        return $this;
    }

    /**
     * Validation: Value must be an array
     */
    public function array(): self
    {
        $this->validationRules['array'] = true;
        return $this;
    }

    /**
     * Validation: Value must be a valid URL
     */
    public function url(): self
    {
        $this->validationRules['url'] = true;
        return $this;
    }

    /**
     * Validation: Value must be a valid IP address
     *
     * @param string|null $version 'ipv4' or 'ipv6' or null for both
     */
    public function ip(?string $version = null): self
    {
        $this->validationRules['ip'] = $version ?? true;
        return $this;
    }

    /**
     * Validation: Value must be valid JSON
     */
    public function json(): self
    {
        $this->validationRules['json'] = true;
        return $this;
    }

    /**
     * Validation: Value must contain only alphabetic characters
     */
    public function alpha(): self
    {
        $this->validationRules['alpha'] = true;
        return $this;
    }

    /**
     * Validation: Value must contain only alphanumeric characters
     */
    public function alphaNumeric(): self
    {
        $this->validationRules['alpha_numeric'] = true;
        return $this;
    }

    /**
     * Validation: Value must be numeric digits with exact length
     *
     * @param int|null $length Exact length or null to just check if digits
     */
    public function digits(?int $length = null): self
    {
        $this->validationRules['digits'] = $length ?? true;
        return $this;
    }

    /**
     * Validation: Value must be a valid date
     */
    public function date(): self
    {
        $this->validationRules['date'] = true;
        return $this;
    }

    /**
     * Validation: Value must match a specific date format
     *
     * @param string $format Date format (e.g., 'Y-m-d', 'd/m/Y')
     */
    public function dateFormat(string $format): self
    {
        $this->validationRules['date_format'] = $format;
        return $this;
    }

    /**
     * Validation: Value must be a date before another date
     *
     * @param string $date Date to compare against
     */
    public function before(string $date): self
    {
        $this->validationRules['before'] = $date;
        return $this;
    }

    /**
     * Validation: Value must be a date after another date
     *
     * @param string $date Date to compare against
     */
    public function after(string $date): self
    {
        $this->validationRules['after'] = $date;
        return $this;
    }

    /**
     * Validation: Value must be between two values
     *
     * @param int|float $min Minimum value
     * @param int|float $max Maximum value
     */
    public function between(int|float $min, int|float $max): self
    {
        $this->validationRules['between'] = [$min, $max];
        return $this;
    }

    /**
     * Validation: Value must match another field (e.g., password confirmation)
     *
     * @param string|null $field Field name to match (defaults to {name}_confirmation)
     */
    public function confirmed(?string $field = null): self
    {
        $this->validationRules['confirmed'] = $field ?? $this->name . '_confirmation';
        return $this;
    }

    /**
     * Validation: Value must be in a list of allowed values
     *
     * @param array $values Allowed values
     */
    public function in(array $values): self
    {
        $this->validationRules['in'] = $values;
        return $this;
    }

    /**
     * Validation: Value must not be in a list of disallowed values
     *
     * @param array $values Disallowed values
     */
    public function notIn(array $values): self
    {
        $this->validationRules['not_in'] = $values;
        return $this;
    }

    /**
     * Validation: Value must be unique in database table
     *
     * @param string $table Table name
     * @param string|null $column Column name (defaults to field name)
     * @param mixed $except ID to except (for updates)
     * @param string $idColumn ID column name (defaults to 'id')
     */
    public function unique(
        string $table,
        ?string $column = null,
        mixed $except = null,
        string $idColumn = 'id'
    ): self {
        $this->validationRules['unique'] = [
            'table' => $table,
            'column' => $column ?? $this->name,
            'except' => $except,
            'idColumn' => $idColumn,
        ];
        return $this;
    }

    /**
     * Validation: Value must exist in database table
     *
     * @param string $table Table name
     * @param string|null $column Column name (defaults to field name)
     */
    public function exists(string $table, ?string $column = null): self
    {
        $this->validationRules['exists'] = [
            'table' => $table,
            'column' => $column ?? $this->name,
        ];
        return $this;
    }

    /**
     * Validation: Match regex pattern (alias for pattern())
     *
     * @param string $regex Regular expression pattern
     * @param string|null $message Custom error message
     */
    public function regex(string $regex, ?string $message = null): self
    {
        return $this->pattern($regex, $message);
    }

    // ========== CheckboxTree Methods ==========

    /**
     * Set tree structure for checkbox tree
     *
     * @param array $tree Hierarchical array structure
     */
    public function setTree(array $tree): self
    {
        $this->tree = $tree;
        return $this;
    }

    /**
     * Set tree mode (cascade or independent)
     */
    public function setTreeMode(string $mode): self
    {
        $this->treeMode = $mode;
        return $this;
    }

    /**
     * Get tree structure
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * Get tree mode
     */
    public function getTreeMode(): string
    {
        return $this->treeMode;
    }

    // ========== Repeater Methods ==========

    /**
     * Set repeater field template
     */
    public function setRepeaterFields(FormBuilder $fields): self
    {
        $this->repeaterFields = $fields;
        return $this;
    }

    /**
     * Set minimum number of rows
     */
    public function minRows(int $min): self
    {
        $this->repeaterMin = $min;
        return $this;
    }

    /**
     * Set maximum number of rows
     */
    public function maxRows(int $max): self
    {
        $this->repeaterMax = $max;
        return $this;
    }

    /**
     * Get repeater fields
     */
    public function getRepeaterFields(): ?FormBuilder
    {
        return $this->repeaterFields;
    }

    // ========== Picker Methods ==========

    /**
     * Enable built-in picker (default)
     */
    public function enablePicker(): self
    {
        $this->pickerEnabled = true;
        return $this;
    }

    /**
     * Disable built-in picker (use custom picker or native HTML5 input)
     */
    public function disablePicker(): self
    {
        $this->pickerEnabled = false;
        return $this;
    }

    /**
     * Set picker locale
     */
    public function setPickerLocale(array $locale): self
    {
        $this->pickerOptions['locale'] = $locale;
        return $this;
    }

    /**
     * Set picker options
     *
     * For DatePicker: format, minDate, maxDate, disabledDates, weekStart, etc.
     * For TimePicker: format (12/24), showSeconds, minTime, maxTime, step, etc.
     * For RangeSlider: min, max, step, prefix, suffix, dual, etc.
     */
    public function setPickerOptions(array $options): self
    {
        $this->pickerOptions = array_merge($this->pickerOptions, $options);
        return $this;
    }

    /**
     * Get picker enabled status
     */
    public function isPickerEnabled(): bool
    {
        return $this->pickerEnabled && $this->type->supportsPicker();
    }

    /**
     * Get picker options
     */
    public function getPickerOptions(): array
    {
        return $this->pickerOptions;
    }

    // ========== Data Transformer Methods (v2.3.1) ==========

    /**
     * Add data transformer
     *
     * Transformers are applied in the order they are added.
     * Multiple transformers can be chained for complex transformations.
     *
     * Example:
     * ```php
     * $form->addDate('birthday', 'Birthday')
     *     ->addTransformer(new DateTimeToStringTransformer('Y-m-d'))
     *     ->add();
     *
     * $form->addText('tags', 'Tags')
     *     ->addTransformer(new StringToArrayTransformer(','))
     *     ->add();
     * ```
     *
     * @param DataTransformerInterface $transformer The data transformer
     */
    public function addTransformer(DataTransformerInterface $transformer): self
    {
        $this->dataTransformers[] = $transformer;
        return $this;
    }

    /**
     * Set data transformers (replaces existing transformers)
     *
     * @param array<DataTransformerInterface> $transformers Array of data transformers
     */
    public function setTransformers(array $transformers): self
    {
        foreach ($transformers as $transformer) {
            if (!$transformer instanceof DataTransformerInterface) {
                throw new \InvalidArgumentException(
                    'All transformers must implement DataTransformerInterface'
                );
            }
        }

        $this->dataTransformers = $transformers;
        return $this;
    }

    /**
     * Get all data transformers
     *
     * @return array<DataTransformerInterface>
     */
    public function getTransformers(): array
    {
        return $this->dataTransformers;
    }

    /**
     * Check if this input has any transformers
     */
    public function hasTransformers(): bool
    {
        return !empty($this->dataTransformers);
    }

    /**
     * Transform value from model to view format
     *
     * Applies all transformers in order (model -> view).
     *
     * @param mixed $value The model value
     * @return mixed The transformed view value
     * @internal Used by FormBuilder
     */
    public function transformValue(mixed $value): mixed
    {
        foreach ($this->dataTransformers as $transformer) {
            $value = $transformer->transform($value);
        }

        return $value;
    }

    /**
     * Transform value from view to model format
     *
     * Applies all transformers in reverse order (view -> model).
     *
     * @param mixed $value The view value
     * @return mixed The transformed model value
     * @internal Used by FormBuilder
     */
    public function reverseTransformValue(mixed $value): mixed
    {
        // Apply transformers in reverse order
        $transformers = array_reverse($this->dataTransformers);

        foreach ($transformers as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        return $value;
    }

    /**
     * Finish building this input and return to FormBuilder
     */
    public function add(): FormBuilder
    {
        $this->formBuilder->addInputBuilder($this);
        return $this->formBuilder;
    }

    /**
     * Get input configuration as array
     */
    public function toArray(): array
    {
        $config = [
            'name' => $this->name,
            'type' => $this->type->value,
            'label' => $this->label ?? ucfirst(str_replace('_', ' ', $this->name)),
            'value' => $this->value,
            'placeholder' => $this->placeholder,
            'helpText' => $this->helpText,
            'attributes' => $this->buildAttributes(),
            'wrapperAttributes' => $this->wrapperAttributes,
            'labelAttributes' => $this->labelAttributes,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'readonly' => $this->readonly,
            'validationRules' => $this->validationRules,
            'dependencies' => $this->dependencies,
        ];

        if ($this->type->requiresOptions()) {
            $config['options'] = $this->options;
        }

        if ($this->defaultValue !== null) {
            $config['defaultValue'] = $this->defaultValue;
        }

        // CheckboxTree specific
        if ($this->type === InputType::CHECKBOX_TREE) {
            $config['tree'] = $this->tree;
            $config['treeMode'] = $this->treeMode;
        }

        // Repeater specific
        if ($this->type === InputType::REPEATER) {
            $config['repeaterFields'] = $this->repeaterFields;
            $config['repeaterMin'] = $this->repeaterMin;
            $config['repeaterMax'] = $this->repeaterMax;
        }

        // Picker specific
        if ($this->type->supportsPicker()) {
            $config['pickerEnabled'] = $this->pickerEnabled;
            $config['pickerOptions'] = $this->pickerOptions;
            $config['pickerType'] = $this->type->getPickerType();
        }

        return $config;
    }

    /**
     * Build final HTML attributes array
     */
    private function buildAttributes(): array
    {
        $attrs = $this->attributes;
        $attrs['name'] = $this->name;
        $attrs['id'] = $attrs['id'] ?? $this->name;

        if ($this->required) {
            $attrs['required'] = 'required';
        }

        if ($this->disabled) {
            $attrs['disabled'] = 'disabled';
        }

        if ($this->readonly) {
            $attrs['readonly'] = 'readonly';
        }

        if ($this->placeholder) {
            $attrs['placeholder'] = $this->placeholder;
        }

        return $attrs;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): InputType
    {
        return $this->type;
    }

    /**
     * Set input type (v2.5.0)
     *
     * Allows types to change the InputType
     *
     * @param InputType $type New input type
     * @return self
     * @since 2.5.0
     */
    public function setType(InputType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
