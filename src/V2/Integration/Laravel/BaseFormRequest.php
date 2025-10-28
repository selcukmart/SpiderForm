<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Laravel;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Event\{EventSubscriberInterface, FormEvent, FormEvents};
use FormGenerator\V2\Form\FormTypeInterface;

/**
 * Base Form Request for Laravel
 *
 * Integrates FormGenerator with Laravel's form request validation.
 * Combines Laravel's authorization and validation with FormGenerator's
 * class-based form building.
 *
 * Example:
 * ```php
 * class UserRegistrationRequest extends BaseFormRequest
 * {
 *     public function authorize(): bool
 *     {
 *         return true;
 *     }
 *
 *     public function rules(): array
 *     {
 *         return [
 *             'username' => 'required|min:3|max:20|unique:users',
 *             'email' => 'required|email|unique:users',
 *             'password' => 'required|min:8|confirmed',
 *         ];
 *     }
 *
 *     public function buildForm(FormBuilder $builder): void
 *     {
 *         $builder
 *             ->addText('username', 'Username')
 *                 ->required()
 *                 ->minLength(3)
 *                 ->maxLength(20)
 *                 ->add()
 *
 *             ->addEmail('email', 'Email')->required()->add()
 *
 *             ->addPassword('password', 'Password')
 *                 ->required()
 *                 ->minLength(8)
 *                 ->add()
 *
 *             ->addPassword('password_confirmation', 'Confirm Password')
 *                 ->required()
 *                 ->add()
 *
 *             ->addSubmit('register', 'Register');
 *     }
 * }
 *
 * // In controller:
 * public function register(UserRegistrationRequest $request)
 * {
 *     // Validation already passed
 *     $validated = $request->validated();
 *
 *     // Get form HTML
 *     $formHtml = $request->getFormHtml();
 * }
 * ```
 */
abstract class BaseFormRequest
{
    protected ?FormBuilder $formBuilder = null;
    protected array $formOptions = [];

    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    abstract public function authorize(): bool;

    /**
     * Get validation rules (Laravel validation rules)
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Build the form
     *
     * @param FormBuilder $builder
     */
    abstract public function buildForm(FormBuilder $builder): void;

    /**
     * Get form name
     *
     * @return string
     */
    public function getFormName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $className);
        $name = strtolower($name ?? '');
        return preg_replace('/(request|_form)$/', '', $name);
    }

    /**
     * Get form builder instance
     *
     * @return FormBuilder
     */
    public function getFormBuilder(): FormBuilder
    {
        if ($this->formBuilder === null) {
            $this->formBuilder = FormBuilder::create($this->getFormName());

            // Apply default options
            $this->applyFormOptions($this->formBuilder);

            // Build form
            $this->buildForm($this->formBuilder);

            // Attach event listeners if provided
            if ($this instanceof EventSubscriberInterface) {
                $this->formBuilder->addSubscriber($this);
            }
        }

        return $this->formBuilder;
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml(): string
    {
        return $this->getFormBuilder()->build();
    }

    /**
     * Configure form options
     *
     * Override this method to customize form options
     *
     * @return array
     */
    protected function configureFormOptions(): array
    {
        return [
            'method' => 'POST',
            'csrf_protection' => true,
            'validation' => true,
        ];
    }

    /**
     * Apply form options to builder
     *
     * @param FormBuilder $builder
     */
    protected function applyFormOptions(FormBuilder $builder): void
    {
        $options = array_merge($this->configureFormOptions(), $this->formOptions);

        if (isset($options['method'])) {
            $builder->setMethod($options['method']);
        }

        if (isset($options['action'])) {
            $builder->setAction($options['action']);
        }

        if (isset($options['csrf_protection'])) {
            $builder->enableCsrf($options['csrf_protection']);
        }

        if (isset($options['validation'])) {
            $builder->enableValidation($options['validation']);
        }

        if (isset($options['theme'])) {
            $builder->setTheme($options['theme']);
        }

        if (isset($options['renderer'])) {
            $builder->setRenderer($options['renderer']);
        }

        if (isset($options['direction'])) {
            $builder->setDirection($options['direction']);
        }

        if (isset($options['locale'])) {
            $builder->setLocale($options['locale']);
        }
    }

    /**
     * Set form options
     *
     * @param array $options
     * @return self
     */
    public function setFormOptions(array $options): self
    {
        $this->formOptions = $options;
        return $this;
    }

    /**
     * Get custom validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attribute names for validation errors
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }
}
