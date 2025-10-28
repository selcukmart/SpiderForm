<?php

declare(strict_types=1);

namespace FormGenerator\V2\Builder;

use FormGenerator\V2\Contracts\{
    BuilderInterface,
    DataProviderInterface,
    InputType,
    OutputFormat,
    RendererInterface,
    ScopeType,
    SecurityInterface,
    TextDirection,
    ThemeInterface,
    ValidatorInterface
};
use FormGenerator\V2\Validation\{ValidationManager, SymfonyValidator};
use FormGenerator\V2\Event\{EventDispatcher, FormEvent, FormEvents, EventSubscriberInterface};
use FormGenerator\V2\Form\{
    FormTypeInterface,
    Form,
    FormInterface,
    FormConfig,
    FormCollection
};
use FormGenerator\V2\DataMapper\FormDataMapper;
use FormGenerator\V2\Type\{
    TypeRegistry,
    TypeExtensionRegistry,
    OptionsResolver
};
use FormGenerator\V2\Translation\TranslatorInterface;
use FormGenerator\V2\Security\{CsrfProtection, CsrfTokenManager};

/**
 * Form Builder - Main Entry Point with Chain Pattern
 *
 * Usage Example:
 * <code>
 * $form = FormBuilder::create('user_form')
 *     ->setAction('/users/save')
 *     ->setMethod('POST')
 *     ->addText('name')->required()->minLength(3)->add()
 *     ->addEmail('email')->required()->add()
 *     ->addSelect('country')->options($countries)->add()
 *     ->addSubmit('save', 'Save User')
 *     ->build();
 * </code>
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormBuilder implements BuilderInterface
{
    private string $name;
    private string $method = 'POST';
    private string $action = '';
    private ScopeType $scope = ScopeType::ADD;
    private array $attributes = [];
    private array $inputs = [];
    private array $sections = [];
    private ?Section $currentSection = null;
    private ?DataProviderInterface $dataProvider = null;
    private ?RendererInterface $renderer = null;
    private ?ThemeInterface $theme = null;
    private ?SecurityInterface $security = null;
    private ?ValidatorInterface $validator = null;
    private bool $enableCsrf = true;
    private bool $enableValidation = true;
    private bool $enableClientSideValidation = true;
    private array $data = [];
    private ?string $enctype = null;
    private ?object $dto = null;
    private bool $stepperEnabled = false;
    private array $stepperOptions = [];
    private ?TextDirection $direction = null;
    private ?array $locale = null;
    private EventDispatcher $eventDispatcher;
    private array $nestedForms = []; // v2.4.0: Nested forms
    private array $collections = []; // v2.4.0: Collections
    private array $constraints = []; // v2.7.0: Form-level constraints
    private array $validationGroups = []; // v2.7.0: Validation groups

    // v3.0.0: i18n support
    private static ?TranslatorInterface $translator = null;
    private ?string $formLocale = null;

    // v3.0.0: CSRF configuration
    private ?string $csrfTokenId = null;
    private string $csrfFieldName = '_csrf_token';
    private ?CsrfProtection $csrfProtection = null;

    private function __construct(string $name)
    {
        $this->name = $name;
        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * Create new form builder instance
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Create form from FormType class (Symfony-style)
     *
     * Example:
     * ```php
     * $form = FormBuilder::createFromType(new UserRegistrationForm(), [
     *     'csrf_protection' => true,
     *     'action' => '/register',
     * ]);
     * ```
     *
     * @param FormTypeInterface $formType Form type instance
     * @param array $options Form options (merged with form type's default options)
     * @return self Form builder instance
     */
    public static function createFromType(FormTypeInterface $formType, array $options = []): self
    {
        // Get form name from type
        $formName = $formType->getName();

        // Create builder instance
        $builder = new self($formName);

        // Merge options
        $defaultOptions = $formType->configureOptions();
        $mergedOptions = array_merge($defaultOptions, $options);

        // Apply options to builder
        if (isset($mergedOptions['method'])) {
            $builder->setMethod($mergedOptions['method']);
        }

        if (isset($mergedOptions['action'])) {
            $builder->setAction($mergedOptions['action']);
        }

        if (isset($mergedOptions['attr'])) {
            $builder->attributes($mergedOptions['attr']);
        }

        if (isset($mergedOptions['csrf_protection'])) {
            $builder->enableCsrf($mergedOptions['csrf_protection']);
        }

        if (isset($mergedOptions['validation'])) {
            $builder->enableValidation($mergedOptions['validation']);
        }

        // Build form using the form type
        $formType->buildForm($builder, $mergedOptions);

        return $builder;
    }

    /**
     * Set form name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get form name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set form method (GET/POST)
     */
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set form action URL
     */
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set form scope (add/edit/view)
     */
    public function setScope(ScopeType $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Set form as edit mode
     */
    public function edit(): self
    {
        $this->scope = ScopeType::EDIT;
        return $this;
    }

    /**
     * Set form as view mode
     */
    public function view(): self
    {
        $this->scope = ScopeType::VIEW;
        return $this;
    }

    /**
     * Set data provider
     */
    public function setDataProvider(DataProviderInterface $provider): self
    {
        $this->dataProvider = $provider;
        return $this;
    }

    /**
     * Set renderer engine
     */
    public function setRenderer(RendererInterface $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Set theme
     */
    public function setTheme(ThemeInterface $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Set security handler
     */
    public function setSecurity(SecurityInterface $security): self
    {
        $this->security = $security;
        return $this;
    }

    /**
     * Set validator
     */
    public function setValidator(ValidatorInterface $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Enable/disable validation
     */
    public function enableValidation(bool $enable = true): self
    {
        $this->enableValidation = $enable;
        return $this;
    }

    /**
     * Enable/disable client-side (JavaScript) validation
     */
    public function enableClientSideValidation(bool $enable = true): self
    {
        $this->enableClientSideValidation = $enable;
        return $this;
    }

    /**
     * Set custom animation options for dependencies
     *
     * @param array $options Options: enabled (bool), duration (int ms), type (fade|slide|none), easing (string)
     */
    public function setDependencyAnimation(array $options): self
    {
        DependencyManager::setAnimationOptions($this->name, $options);
        return $this;
    }

    /**
     * Disable dependency animations
     */
    public function disableDependencyAnimation(): self
    {
        DependencyManager::setAnimationOptions($this->name, ['enabled' => false]);
        return $this;
    }

    /**
     * Enable form stepper/wizard mode
     * Sections will become steps in the stepper
     *
     * @param array $options Options: layout (horizontal|vertical), mode (linear|non-linear), animation (bool), validateOnNext (bool), animationDuration (int)
     */
    public function enableStepper(array $options = []): self
    {
        $this->stepperEnabled = true;
        $this->stepperOptions = array_merge([
            'layout' => StepperManager::LAYOUT_HORIZONTAL,
            'mode' => StepperManager::MODE_LINEAR,
            'startIndex' => 0,
            'animation' => true,
            'animationDuration' => 300,
            'validateOnNext' => true,
            'showNavigationButtons' => true,
        ], $options);
        return $this;
    }

    /**
     * Disable form stepper mode
     */
    public function disableStepper(): self
    {
        $this->stepperEnabled = false;
        $this->stepperOptions = [];
        return $this;
    }

    /**
     * Check if stepper mode is enabled
     */
    public function isStepperEnabled(): bool
    {
        return $this->stepperEnabled;
    }

    /**
     * Set text direction for the form (LTR or RTL)
     * This will apply to all inputs and pickers automatically
     *
     * @param TextDirection $direction Text direction (LTR or RTL)
     */
    public function setDirection(TextDirection $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * Get text direction for the form
     */
    public function getDirection(): ?TextDirection
    {
        return $this->direction;
    }

    /**
     * Set locale for the form (for pickers and localization)
     * This will apply to all pickers (date, time, datetime, range) automatically
     *
     * @param array $locale Locale array (e.g., DatePickerManager::LOCALE_EN)
     */
    public function setLocale(array $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get locale for the form
     */
    public function getLocale(): ?array
    {
        return $this->locale;
    }

    // ========== Event System Methods ==========

    /**
     * Add event listener
     *
     * @param string $eventName Event name (use FormEvents constants)
     * @param callable $listener Listener callable
     * @param int $priority Priority (higher = earlier execution)
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): self
    {
        $this->eventDispatcher->addEventListener($eventName, $listener, $priority);
        return $this;
    }

    /**
     * Add event subscriber
     *
     * @param EventSubscriberInterface $subscriber Event subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): self
    {
        $this->eventDispatcher->addSubscriber($subscriber);
        return $this;
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcher Event dispatcher instance
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * Enable/disable CSRF protection
     */
    public function enableCsrf(bool $enable = true): self
    {
        $this->enableCsrf = $enable;
        return $this;
    }

    /**
     * Set CSRF token ID (v3.0.0)
     *
     * @param string $tokenId Token identifier
     */
    public function setCsrfTokenId(string $tokenId): self
    {
        $this->csrfTokenId = $tokenId;
        return $this;
    }

    /**
     * Set CSRF field name (v3.0.0)
     *
     * @param string $fieldName Field name for CSRF token
     */
    public function setCsrfFieldName(string $fieldName): self
    {
        $this->csrfFieldName = $fieldName;
        return $this;
    }

    /**
     * Get CSRF protection instance (v3.0.0)
     */
    public function getCsrfProtection(): CsrfProtection
    {
        if ($this->csrfProtection === null) {
            $this->csrfProtection = new CsrfProtection();
        }

        return $this->csrfProtection;
    }

    /**
     * Set translator for all forms (v3.0.0)
     *
     * @param TranslatorInterface $translator Translator instance
     */
    public static function setTranslator(TranslatorInterface $translator): void
    {
        self::$translator = $translator;
    }

    /**
     * Get translator (v3.0.0)
     */
    public static function getTranslator(): ?TranslatorInterface
    {
        return self::$translator;
    }

    /**
     * Set locale for this form (v3.0.0)
     *
     * @param string $locale Locale code (e.g., 'en_US', 'tr_TR')
     */
    public function setLocale(string $locale): self
    {
        $this->formLocale = $locale;

        if (self::$translator !== null) {
            self::$translator->setLocale($locale);
        }

        return $this;
    }

    /**
     * Get form locale (v3.0.0)
     */
    public function getFormLocale(): ?string
    {
        return $this->formLocale;
    }

    /**
     * Translate a key (v3.0.0)
     *
     * @param string $key Translation key
     * @param array $parameters Parameters for interpolation
     * @return string Translated message or key if no translator
     */
    public function trans(string $key, array $parameters = []): string
    {
        if (self::$translator === null) {
            return $key;
        }

        return self::$translator->trans($key, $parameters, $this->formLocale);
    }

    /**
     * Set form data (for edit/view mode)
     */
    public function setData(array $data): self
    {
        // Dispatch PRE_SET_DATA event
        $preSetDataEvent = new FormEvent($this, $data);
        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $preSetDataEvent);

        // Get potentially modified data from event
        $modifiedData = $preSetDataEvent->getData();
        if (is_array($modifiedData)) {
            $data = $modifiedData;
        }

        $this->data = $data;

        // Dispatch POST_SET_DATA event
        $postSetDataEvent = new FormEvent($this, $this->data);
        $this->eventDispatcher->dispatch(FormEvents::POST_SET_DATA, $postSetDataEvent);

        return $this;
    }

    /**
     * Load data from provider by ID
     */
    public function loadData(mixed $id): self
    {
        if ($this->dataProvider === null) {
            throw new \RuntimeException('Data provider not set');
        }

        $data = $this->dataProvider->findById($id);
        if ($data !== null) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Set DTO/Entity object (Symfony DTO support)
     * Automatically extracts data and validation rules
     */
    public function setDto(object $dto): self
    {
        $this->dto = $dto;

        // Extract data from DTO
        $reflection = new \ReflectionClass($dto);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($dto);
            if ($value !== null) {
                $this->data[$property->getName()] = $value;
            }
        }

        // Extract validation rules if using SymfonyValidator
        if ($this->validator instanceof SymfonyValidator) {
            $rules = $this->validator->extractRulesFromObject($dto);
            foreach ($rules as $fieldName => $fieldRules) {
                // Apply rules to corresponding inputs
                foreach ($this->inputs as $input) {
                    if ($input->getName() === $fieldName) {
                        // Rules will be applied during validation
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get DTO object
     */
    public function getDto(): ?object
    {
        return $this->dto;
    }

    /**
     * Validate form data using Laravel-style validation
     *
     * This method extracts validation rules from all form inputs and validates
     * the provided data using the new Laravel-style Validator.
     *
     * Example:
     * ```php
     * try {
     *     $validated = $form->validateData($_POST);
     *     // Data is valid, use $validated
     * } catch (ValidationException $e) {
     *     $errors = $e->errors();
     *     // Display errors
     * }
     * ```
     *
     * @param array $data Data to validate
     * @param array $customMessages Custom error messages (optional)
     * @param array $customAttributes Custom attribute names (optional)
     * @param \PDO|null $dbConnection Database connection for unique/exists rules (optional)
     * @return array Validated data
     * @throws \FormGenerator\V2\Validation\ValidationException
     */
    public function validateData(
        array $data,
        array $customMessages = [],
        array $customAttributes = [],
        ?\PDO $dbConnection = null
    ): array {
        $rules = $this->extractValidationRules();

        $validator = new \FormGenerator\V2\Validation\Validator(
            $data,
            $rules,
            $customMessages,
            $customAttributes
        );

        if ($dbConnection !== null) {
            $validator->setDatabaseConnection($dbConnection);
        }

        // Dispatch PRE_SUBMIT event
        $preSubmitEvent = new FormEvent($this, $data);
        $this->eventDispatcher->dispatch(FormEvents::PRE_SUBMIT, $preSubmitEvent);

        try {
            // Perform validation
            $validated = $validator->validate();

            // Dispatch VALIDATION_SUCCESS event
            $validationSuccessEvent = new FormEvent($this, $validated);
            $this->eventDispatcher->dispatch(FormEvents::VALIDATION_SUCCESS, $validationSuccessEvent);

            // Dispatch POST_SUBMIT event
            $postSubmitEvent = new FormEvent($this, $validated);
            $this->eventDispatcher->dispatch(FormEvents::POST_SUBMIT, $postSubmitEvent);

            return $validated;
        } catch (\FormGenerator\V2\Validation\ValidationException $e) {
            // Dispatch VALIDATION_ERROR event
            $validationErrorEvent = new FormEvent($this, $e->errors());
            $this->eventDispatcher->dispatch(FormEvents::VALIDATION_ERROR, $validationErrorEvent);

            throw $e;
        }
    }

    /**
     * Extract validation rules from all form inputs
     *
     * Converts InputBuilder validation rules to Laravel-style rule strings.
     *
     * @return array Validation rules
     */
    private function extractValidationRules(): array
    {
        $rules = [];

        foreach ($this->inputs as $input) {
            $inputRules = $input->getValidationRules();

            if (empty($inputRules)) {
                continue;
            }

            $fieldName = $input->getName();
            $ruleParts = [];

            foreach ($inputRules as $ruleName => $ruleValue) {
                // Handle Laravel-style rule strings
                if ($ruleName === 'rules' && is_string($ruleValue)) {
                    $ruleParts[] = $ruleValue;
                    continue;
                }

                // Convert InputBuilder rules to Laravel-style rules
                switch ($ruleName) {
                    case 'required':
                        $ruleParts[] = 'required';
                        break;
                    case 'email':
                        $ruleParts[] = 'email';
                        break;
                    case 'minLength':
                    case 'min':
                        $ruleParts[] = "min:$ruleValue";
                        break;
                    case 'maxLength':
                    case 'max':
                        $ruleParts[] = "max:$ruleValue";
                        break;
                    case 'numeric':
                        $ruleParts[] = 'numeric';
                        break;
                    case 'integer':
                        $ruleParts[] = 'integer';
                        break;
                    case 'string':
                        $ruleParts[] = 'string';
                        break;
                    case 'boolean':
                        $ruleParts[] = 'boolean';
                        break;
                    case 'array':
                        $ruleParts[] = 'array';
                        break;
                    case 'url':
                        $ruleParts[] = 'url';
                        break;
                    case 'ip':
                        if ($ruleValue === true) {
                            $ruleParts[] = 'ip';
                        } else {
                            $ruleParts[] = "ip:$ruleValue";
                        }
                        break;
                    case 'json':
                        $ruleParts[] = 'json';
                        break;
                    case 'alpha':
                        $ruleParts[] = 'alpha';
                        break;
                    case 'alpha_numeric':
                        $ruleParts[] = 'alpha_numeric';
                        break;
                    case 'digits':
                        if ($ruleValue === true) {
                            $ruleParts[] = 'digits';
                        } else {
                            $ruleParts[] = "digits:$ruleValue";
                        }
                        break;
                    case 'date':
                        $ruleParts[] = 'date';
                        break;
                    case 'date_format':
                        $ruleParts[] = "date_format:$ruleValue";
                        break;
                    case 'before':
                        $ruleParts[] = "before:$ruleValue";
                        break;
                    case 'after':
                        $ruleParts[] = "after:$ruleValue";
                        break;
                    case 'between':
                        if (is_array($ruleValue)) {
                            $ruleParts[] = 'between:' . implode(',', $ruleValue);
                        }
                        break;
                    case 'confirmed':
                        $ruleParts[] = "confirmed:$ruleValue";
                        break;
                    case 'in':
                        if (is_array($ruleValue)) {
                            $ruleParts[] = 'in:' . implode(',', $ruleValue);
                        }
                        break;
                    case 'not_in':
                        if (is_array($ruleValue)) {
                            $ruleParts[] = 'not_in:' . implode(',', $ruleValue);
                        }
                        break;
                    case 'unique':
                        if (is_array($ruleValue)) {
                            $params = [
                                $ruleValue['table'],
                                $ruleValue['column'],
                                $ruleValue['except'] ?? '',
                                $ruleValue['idColumn'] ?? 'id'
                            ];
                            $ruleParts[] = 'unique:' . implode(',', $params);
                        }
                        break;
                    case 'exists':
                        if (is_array($ruleValue)) {
                            $ruleParts[] = 'exists:' . $ruleValue['table'] . ',' . $ruleValue['column'];
                        }
                        break;
                    case 'pattern':
                        if (is_array($ruleValue) && isset($ruleValue['regex'])) {
                            $ruleParts[] = 'regex:' . $ruleValue['regex'];
                        }
                        break;
                }
            }

            if (!empty($ruleParts)) {
                $rules[$fieldName] = implode('|', $ruleParts);
            }
        }

        return $rules;
    }

    /**
     * Validate form data against DTO
     */
    public function validateDto(array $data): array
    {
        if ($this->dto === null) {
            throw new \RuntimeException('DTO not set. Use setDto() first.');
        }

        if (!$this->validator instanceof SymfonyValidator) {
            throw new \RuntimeException('SymfonyValidator required for DTO validation');
        }

        // Hydrate DTO with data
        $reflection = new \ReflectionClass($this->dto);
        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($this->dto, $value);
            }
        }

        // Validate DTO
        $result = $this->validator->validateObject($this->dto);

        return $result->getErrors();
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
     * Set enctype (for file uploads)
     */
    public function setEnctype(string $enctype): self
    {
        $this->enctype = $enctype;
        return $this;
    }

    /**
     * Enable multipart for file uploads
     */
    public function multipart(): self
    {
        $this->enctype = 'multipart/form-data';
        return $this;
    }

    // ========== Section Methods ==========

    /**
     * Start a new section
     *
     * @param string $title Section title
     * @param string $description Optional description (supports HTML)
     */
    public function addSection(string $title, string $description = ''): self
    {
        $section = new Section($title);
        if ($description !== '') {
            $section->setDescription($description);
        }

        $this->currentSection = $section;
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Set HTML content for current section
     */
    public function setSectionHtml(string $html): self
    {
        if ($this->currentSection !== null) {
            $this->currentSection->setHtmlContent($html);
        }

        return $this;
    }

    /**
     * Set attributes for current section
     */
    public function setSectionAttributes(array $attributes): self
    {
        if ($this->currentSection !== null) {
            $this->currentSection->setAttributes($attributes);
        }

        return $this;
    }

    /**
     * Set classes for current section
     */
    public function setSectionClasses(array $classes): self
    {
        if ($this->currentSection !== null) {
            $this->currentSection->setClasses($classes);
        }

        return $this;
    }

    /**
     * Make current section collapsible
     */
    public function collapsibleSection(bool $collapsed = false): self
    {
        if ($this->currentSection !== null) {
            $this->currentSection->collapsible($collapsed);
        }

        return $this;
    }

    /**
     * End current section
     */
    public function endSection(): self
    {
        $this->currentSection = null;
        return $this;
    }

    // ========== Input Building Methods ==========

    /**
     * Add text input
     */
    public function addText(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::TEXT, $label);
    }

    /**
     * Add email input
     */
    public function addEmail(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::EMAIL, $label);
    }

    /**
     * Add password input
     */
    public function addPassword(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::PASSWORD, $label);
    }

    /**
     * Add textarea
     */
    public function addTextarea(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::TEXTAREA, $label);
    }

    /**
     * Add select dropdown
     */
    public function addSelect(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::SELECT, $label);
    }

    /**
     * Add checkbox
     */
    public function addCheckbox(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::CHECKBOX, $label);
    }

    /**
     * Add radio buttons
     */
    public function addRadio(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::RADIO, $label);
    }

    /**
     * Add file input
     */
    public function addFile(string $name, ?string $label = null): InputBuilder
    {
        $this->multipart(); // Auto-enable multipart
        return $this->createInput($name, InputType::FILE, $label);
    }

    /**
     * Add image input
     */
    public function addImage(string $name, ?string $label = null): InputBuilder
    {
        $this->multipart(); // Auto-enable multipart
        return $this->createInput($name, InputType::IMAGE, $label);
    }

    /**
     * Add hidden input
     */
    public function addHidden(string $name, mixed $value = null): InputBuilder
    {
        $input = $this->createInput($name, InputType::HIDDEN);
        if ($value !== null) {
            $input->value($value);
        }
        return $input;
    }

    /**
     * Add number input
     */
    public function addNumber(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::NUMBER, $label);
    }

    /**
     * Add date input
     */
    public function addDate(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::DATE, $label);
    }

    /**
     * Add datetime input
     */
    public function addDatetime(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::DATETIME, $label);
    }

    /**
     * Add time input
     */
    public function addTime(string $name, ?string $label = null): InputBuilder
    {
        return $this->createInput($name, InputType::TIME, $label);
    }

    /**
     * Add checkbox tree (hierarchical checkboxes)
     *
     * @param string $name Field name
     * @param string|null $label Field label
     * @param array $tree Hierarchical array structure
     * @param string $mode 'cascade' or 'independent'
     *
     * Example tree structure:
     * [
     *     ['value' => 'parent1', 'label' => 'Parent 1', 'children' => [
     *         ['value' => 'child1', 'label' => 'Child 1'],
     *         ['value' => 'child2', 'label' => 'Child 2']
     *     ]],
     *     ['value' => 'parent2', 'label' => 'Parent 2']
     * ]
     */
    public function addCheckboxTree(
        string $name,
        ?string $label = null,
        array $tree = [],
        string $mode = CheckboxTreeManager::MODE_CASCADE
    ): InputBuilder {
        $input = $this->createInput($name, InputType::CHECKBOX_TREE, $label);
        $input->setTree($tree);
        $input->setTreeMode($mode);
        return $input;
    }

    /**
     * Add repeater field group (dynamic add/remove rows)
     *
     * @param string $name Field name
     * @param string|null $label Field label
     * @param callable $callback Callback to define repeatable fields
     *
     * Example:
     * $form->addRepeater('contacts', 'Contact List', function($repeater) {
     *     $repeater->addText('name', 'Name')->add();
     *     $repeater->addEmail('email', 'Email')->add();
     * });
     */
    public function addRepeater(string $name, ?string $label = null, ?callable $callback = null): InputBuilder
    {
        $input = $this->createInput($name, InputType::REPEATER, $label);
        if ($callback !== null) {
            // Create a temporary form builder for repeater fields
            $repeaterBuilder = new FormBuilder($name . '_template');
            $repeaterBuilder->setTheme($this->theme);
            $repeaterBuilder->setRenderer($this->renderer);

            $callback($repeaterBuilder);

            $input->setRepeaterFields($repeaterBuilder);
        }
        return $input;
    }

    /**
     * Add submit button
     */
    public function addSubmit(string $name = 'submit', ?string $label = null): self
    {
        $input = $this->createInput($name, InputType::SUBMIT, $label ?? 'Submit');
        $input->add();
        return $this;
    }

    /**
     * Add reset button
     */
    public function addReset(string $name = 'reset', ?string $label = null): self
    {
        $input = $this->createInput($name, InputType::RESET, $label ?? 'Reset');
        $input->add();
        return $this;
    }

    /**
     * Add button
     */
    public function addButton(string $name, string $label): self
    {
        $input = $this->createInput($name, InputType::BUTTON, $label);
        $input->add();
        return $this;
    }

    /**
     * Create input builder
     */
    private function createInput(string $name, InputType $type, ?string $label = null): InputBuilder
    {
        $input = new InputBuilder($this, $name, $type);

        if ($label !== null) {
            $input->label($label);
        }

        // Auto-fill value from data if available
        if (isset($this->data[$name])) {
            $input->value($this->data[$name]);
        }

        return $input;
    }

    /**
     * Apply transformers to form data (model -> view)
     *
     * This is called automatically when building inputs context.
     *
     * @param InputBuilder $input The input builder
     * @param mixed $value The model value
     * @return mixed The transformed view value
     * @internal
     */
    private function applyTransformToValue(InputBuilder $input, mixed $value): mixed
    {
        if (!$input->hasTransformers()) {
            return $value;
        }

        try {
            return $input->transformValue($value);
        } catch (\Exception $e) {
            // Log or handle transformation errors gracefully
            error_log(sprintf(
                'Data transformation error for field "%s": %s',
                $input->getName(),
                $e->getMessage()
            ));

            // Return original value on transformation failure
            return $value;
        }
    }

    /**
     * Apply reverse transformers to submitted data (view -> model)
     *
     * Call this method after form submission to transform the submitted data
     * back to model format.
     *
     * Example:
     * ```php
     * $submittedData = $_POST;
     * $modelData = $form->applyReverseTransform($submittedData);
     * // Now $modelData contains DateTime objects, entities, etc.
     * ```
     *
     * @param array $data The submitted form data (view format)
     * @return array The transformed data (model format)
     */
    public function applyReverseTransform(array $data): array
    {
        $transformedData = [];

        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $fieldName = $input->getName();

            // Skip if field not in submitted data
            if (!array_key_exists($fieldName, $data)) {
                continue;
            }

            $value = $data[$fieldName];

            // Apply reverse transformation if transformers exist
            if ($input->hasTransformers()) {
                try {
                    $value = $input->reverseTransformValue($value);
                } catch (\Exception $e) {
                    // Log transformation error
                    error_log(sprintf(
                        'Reverse data transformation error for field "%s": %s',
                        $fieldName,
                        $e->getMessage()
                    ));

                    // You might want to throw or handle this differently
                    throw new \RuntimeException(sprintf(
                        'Failed to transform field "%s": %s',
                        $fieldName,
                        $e->getMessage()
                    ), 0, $e);
                }
            }

            $transformedData[$fieldName] = $value;
        }

        // Include fields that weren't transformed
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $transformedData)) {
                $transformedData[$key] = $value;
            }
        }

        return $transformedData;
    }

    /**
     * Add input builder to form (called by InputBuilder)
     *
     * @internal
     */
    public function addInputBuilder(InputBuilder $input): void
    {
        $this->inputs[] = [
            'input' => $input,
            'section' => $this->currentSection
        ];
    }

    /**
     * Build and return form as HTML (default)
     */
    public function build(OutputFormat $format = OutputFormat::HTML): string
    {
        return match ($format) {
            OutputFormat::HTML => $this->buildAsHtml(),
            OutputFormat::JSON => $this->buildAsJson(),
            OutputFormat::XML => $this->buildAsXml(),
        };
    }

    /**
     * Build form as HTML
     */
    public function buildAsHtml(): string
    {
        if ($this->renderer === null) {
            throw new \RuntimeException('Renderer not set. Use setRenderer() before building.');
        }

        if ($this->theme === null) {
            throw new \RuntimeException('Theme not set. Use setTheme() before building.');
        }

        // Dispatch PRE_BUILD event
        $preBuildEvent = new FormEvent($this, $this->data, ['format' => 'html']);
        $this->eventDispatcher->dispatch(FormEvents::PRE_BUILD, $preBuildEvent);

        $context = [
            'form' => $this->buildFormContext(),
            'inputs' => $this->buildInputsContext(),
            'csrf_token' => $this->getCsrfToken(),
            'stepper_enabled' => $this->stepperEnabled,
            'stepper_options' => $this->stepperOptions,
        ];

        $formHtml = $this->renderer->render($this->theme->getFormTemplate(), $context);

        // Add dependency management JavaScript if any inputs have dependencies
        if ($this->hasDependencies()) {
            $formHtml .= "\n" . DependencyManager::generateScript($this->name);
        }

        // Add validation JavaScript if validation is enabled
        if ($this->enableValidation && $this->enableClientSideValidation && $this->validator !== null) {
            $fieldsRules = $this->collectValidationRules();
            if (!empty($fieldsRules)) {
                $formHtml .= "\n" . ValidationManager::generateScript(
                    $this->name,
                    $fieldsRules,
                    $this->validator
                );
            }
        }

        // Add stepper JavaScript if stepper is enabled and form has sections
        if ($this->stepperEnabled && !empty($this->sections)) {
            $formHtml .= "\n" . StepperManager::generateScript(
                $this->name,
                $this->stepperOptions
            );
        }

        // Add picker JavaScripts for inputs with pickers enabled
        $formHtml .= $this->generatePickerScripts();

        // Dispatch POST_BUILD event (allows modifying final HTML)
        $postBuildEvent = new FormEvent($this, $formHtml, ['format' => 'html']);
        $this->eventDispatcher->dispatch(FormEvents::POST_BUILD, $postBuildEvent);

        // Get potentially modified HTML from event
        $modifiedHtml = $postBuildEvent->getData();
        if (is_string($modifiedHtml)) {
            $formHtml = $modifiedHtml;
        }

        return $formHtml;
    }

    /**
     * Build form as JSON
     */
    public function buildAsJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        $data = [
            'name' => $this->name,
            'method' => $this->method,
            'action' => $this->action,
            'scope' => $this->scope->value,
            'attributes' => $this->attributes,
            'enctype' => $this->enctype,
            'direction' => $this->direction?->value,
            'locale' => $this->locale,
            'csrf_enabled' => $this->enableCsrf,
            'validation_enabled' => $this->enableValidation,
            'stepper_enabled' => $this->stepperEnabled,
            'stepper_options' => $this->stepperOptions,
            'sections' => array_map(fn($section) => $section->toArray(), $this->sections),
            'inputs' => [],
        ];

        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $inputData = $input->toArray();

            // Add section reference if exists
            if ($item['section'] !== null) {
                $inputData['section'] = $item['section']->toArray();
            }

            $data['inputs'][] = $inputData;
        }

        // Add validation rules
        if ($this->enableValidation) {
            $data['validation_rules'] = $this->collectValidationRules();
        }

        return json_encode($data, $flags);
    }

    /**
     * Build form as XML
     */
    public function buildAsXml(): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><form></form>');

        $xml->addAttribute('name', $this->name);
        $xml->addAttribute('method', $this->method);
        $xml->addAttribute('action', $this->action);
        $xml->addAttribute('scope', $this->scope->value);

        if ($this->enctype) {
            $xml->addAttribute('enctype', $this->enctype);
        }

        if ($this->direction !== null) {
            $xml->addAttribute('direction', $this->direction->value);
        }

        // Add locale information
        if ($this->locale !== null) {
            $localeNode = $xml->addChild('locale');
            foreach ($this->locale as $key => $value) {
                if (is_array($value)) {
                    $arrayNode = $localeNode->addChild($key);
                    foreach ($value as $item) {
                        $arrayNode->addChild('item', htmlspecialchars((string)$item));
                    }
                } else {
                    $localeNode->addChild($key, htmlspecialchars((string)$value));
                }
            }
        }

        // Add attributes
        if (!empty($this->attributes)) {
            $attributesNode = $xml->addChild('attributes');
            foreach ($this->attributes as $key => $value) {
                $attributesNode->addChild($key, htmlspecialchars((string)$value));
            }
        }

        // Add settings
        $settings = $xml->addChild('settings');
        $settings->addChild('csrf_enabled', $this->enableCsrf ? 'true' : 'false');
        $settings->addChild('validation_enabled', $this->enableValidation ? 'true' : 'false');
        $settings->addChild('stepper_enabled', $this->stepperEnabled ? 'true' : 'false');

        // Add sections
        if (!empty($this->sections)) {
            $sectionsNode = $xml->addChild('sections');
            foreach ($this->sections as $section) {
                $sectionData = $section->toArray();
                $sectionNode = $sectionsNode->addChild('section');
                $sectionNode->addChild('title', htmlspecialchars($sectionData['title']));
                if ($sectionData['description']) {
                    $sectionNode->addChild('description', htmlspecialchars($sectionData['description']));
                }
            }
        }

        // Add inputs
        $inputsNode = $xml->addChild('inputs');
        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $inputData = $input->toArray();

            $inputNode = $inputsNode->addChild('input');
            $inputNode->addAttribute('name', $inputData['name']);
            $inputNode->addAttribute('type', $inputData['type']);

            if ($inputData['label']) {
                $inputNode->addChild('label', htmlspecialchars($inputData['label']));
            }

            if ($inputData['value']) {
                $inputNode->addChild('value', htmlspecialchars((string)$inputData['value']));
            }

            if ($inputData['required']) {
                $inputNode->addAttribute('required', 'true');
            }

            // Add validation rules
            if (!empty($inputData['validationRules'])) {
                $rulesNode = $inputNode->addChild('validation_rules');
                foreach ($inputData['validationRules'] as $rule) {
                    $ruleNode = $rulesNode->addChild('rule');
                    $ruleNode->addAttribute('type', $rule['type']);
                    if (isset($rule['value'])) {
                        $ruleNode->addAttribute('value', (string)$rule['value']);
                    }
                }
            }

            // Add options for select/radio/checkbox
            if (!empty($inputData['options'])) {
                $optionsNode = $inputNode->addChild('options');
                foreach ($inputData['options'] as $value => $label) {
                    $optionNode = $optionsNode->addChild('option', htmlspecialchars((string)$label));
                    $optionNode->addAttribute('value', (string)$value);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * Collect validation rules from all inputs
     */
    private function collectValidationRules(): array
    {
        $rules = [];

        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $config = $input->toArray();
            if (!empty($config['validationRules'])) {
                $rules[$config['name']] = $config['validationRules'];
            }
        }

        // Merge with DTO rules if available
        if ($this->dto !== null && $this->validator instanceof SymfonyValidator) {
            $dtoRules = $this->validator->extractRulesFromObject($this->dto);
            $rules = array_merge($dtoRules, $rules); // Input rules override DTO rules
        }

        return $rules;
    }

    /**
     * Check if form has any dependency configurations
     */
    private function hasDependencies(): bool
    {
        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $config = $input->toArray();
            if (!empty($config['dependencies']) || isset($config['attributes']['data-dependency'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate picker scripts for all inputs with pickers enabled
     */
    private function generatePickerScripts(): string
    {
        $scripts = '';

        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $config = $input->toArray();

            // Skip if picker is disabled or not supported
            if (empty($config['pickerEnabled']) || empty($config['pickerType'])) {
                continue;
            }

            $inputId = $config['attributes']['id'] ?? $config['name'];
            $pickerType = $config['pickerType'];
            $pickerOptions = $config['pickerOptions'] ?? [];

            // Auto-apply RTL if form direction is RTL and not explicitly set
            if ($this->direction === TextDirection::RTL && !isset($pickerOptions['rtl'])) {
                $pickerOptions['rtl'] = true;
            }

            // Auto-apply locale if form locale is set and not explicitly set
            if ($this->locale !== null && !isset($pickerOptions['locale'])) {
                $pickerOptions['locale'] = $this->locale;
            }

            // Generate appropriate picker script based on type
            $scripts .= "\n" . match ($pickerType) {
                'date' => DatePickerManager::generateScript($inputId, $pickerOptions),
                'datetime' => DateTimePickerManager::generateScript($inputId, $pickerOptions),
                'time' => TimePickerManager::generateScript($inputId, $pickerOptions),
                'range' => RangeSliderManager::generateScript($inputId, $pickerOptions),
                default => '',
            };
        }

        return $scripts;
    }

    /**
     * Build form context for template
     */
    private function buildFormContext(): array
    {
        $attributes = array_merge([
            'name' => $this->name,
            'id' => $this->name,
            'method' => $this->method,
            'action' => $this->action,
        ], $this->attributes);

        if ($this->enctype !== null) {
            $attributes['enctype'] = $this->enctype;
        }

        // Add direction attribute if set
        if ($this->direction !== null) {
            $attributes['dir'] = $this->direction->value;
        }

        return [
            'name' => $this->name,
            'method' => $this->method,
            'action' => $this->action,
            'scope' => $this->scope->value,
            'attributes' => $attributes,
            'classes' => $this->theme->getFormClasses(),
        ];
    }

    /**
     * Build inputs context for template
     */
    private function buildInputsContext(): array
    {
        $inputsContext = [];
        $currentSectionInputs = [];
        $lastSection = null;
        $serverSideDepsEnabled = ($this->attributes['data-server-side-dependencies'] ?? 'false') === 'true';

        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $section = $item['section'];

            // Dispatch FIELD_PRE_RENDER event
            $preRenderEvent = $this->dispatchFieldEvent($input, \FormGenerator\V2\Event\FieldEvents::FIELD_PRE_RENDER, [
                'form_data' => $this->data,
            ]);

            // If server-side dependency evaluation is enabled, check dependencies
            $shouldRender = true;
            if ($serverSideDepsEnabled) {
                $shouldRender = $this->evaluateFieldDependencies($input);
            }

            // Skip rendering if dependencies not met and server-side evaluation is enabled
            if (!$shouldRender) {
                continue;
            }

            // If we have sections, group inputs by section
            if (!empty($this->sections)) {
                // New section encountered
                if ($section !== $lastSection) {
                    // Save previous section inputs if any
                    if ($lastSection !== null || !empty($currentSectionInputs)) {
                        $inputsContext[] = [
                            'is_section' => true,
                            'section' => $lastSection?->toArray(),
                            'inputs' => $currentSectionInputs
                        ];
                        $currentSectionInputs = [];
                    }
                    $lastSection = $section;
                }
            }

            $inputData = $input->toArray();
            $inputData['template'] = $this->theme->getInputTemplate($input->getType());
            $inputData['classes'] = $this->theme->getInputClasses($input->getType());

            // Apply data transformers (model -> view) before rendering
            if ($input->hasTransformers() && $inputData['value'] !== null) {
                $inputData['value'] = $this->applyTransformToValue($input, $inputData['value']);
            }

            // Sanitize values if security is enabled
            if ($this->security !== null && $inputData['value'] !== null) {
                $inputData['value'] = $this->security->sanitize($inputData['value']);
            }

            // Add direction attribute if set
            if ($this->direction !== null && !isset($inputData['attributes']['dir'])) {
                $inputData['attributes']['dir'] = $this->direction->value;
            }

            // Dispatch FIELD_POST_RENDER event (allows modifying rendered data)
            $postRenderEvent = $this->dispatchFieldEvent($input, \FormGenerator\V2\Event\FieldEvents::FIELD_POST_RENDER, [
                'input_data' => $inputData,
            ]);

            // Get potentially modified input data from event
            if ($postRenderEvent->has('input_data')) {
                $inputData = $postRenderEvent->get('input_data');
            }

            if (!empty($this->sections)) {
                $currentSectionInputs[] = $inputData;
            } else {
                $inputsContext[] = $inputData;
            }
        }

        // Add final section inputs if any
        if (!empty($this->sections) && (!empty($currentSectionInputs) || $lastSection !== null)) {
            $inputsContext[] = [
                'is_section' => true,
                'section' => $lastSection?->toArray(),
                'inputs' => $currentSectionInputs
            ];
        }

        return $inputsContext;
    }

    /**
     * Get CSRF token
     */
    private function getCsrfToken(): ?string
    {
        if (!$this->enableCsrf || $this->security === null) {
            return null;
        }

        return $this->security->generateCsrfToken($this->name);
    }

    /**
     * Get form configuration as array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'method' => $this->method,
            'action' => $this->action,
            'scope' => $this->scope->value,
            'attributes' => $this->attributes,
            'enctype' => $this->enctype,
            'direction' => $this->direction?->value,
            'locale' => $this->locale,
            'csrf_enabled' => $this->enableCsrf,
            'inputs' => array_map(fn($input) => $input->toArray(), $this->inputs),
        ];
    }

    // ========== Field Event System Methods ==========

    /**
     * Dispatch field-level event for a specific field
     *
     * @param InputBuilder $field The field to dispatch event for
     * @param string $eventName Event name (use FieldEvents constants)
     * @param array $context Additional context data
     * @return \FormGenerator\V2\Event\FieldEvent The dispatched event
     */
    public function dispatchFieldEvent(InputBuilder $field, string $eventName, array $context = []): \FormGenerator\V2\Event\FieldEvent
    {
        $event = new \FormGenerator\V2\Event\FieldEvent($field, $this, $context);

        // Get field-specific listeners
        $fieldListeners = $field->getFieldEventListeners();

        if (isset($fieldListeners[$eventName])) {
            foreach ($fieldListeners[$eventName] as $listenerData) {
                if ($event->isPropagationStopped()) {
                    break;
                }

                call_user_func($listenerData['listener'], $event);
            }
        }

        return $event;
    }

    /**
     * Evaluate field dependencies based on current form data
     *
     * This method checks if a field's dependencies are met and dispatches
     * appropriate events (FIELD_SHOW/FIELD_HIDE).
     *
     * @param InputBuilder $field The field to evaluate
     * @return bool True if field should be visible, false otherwise
     */
    public function evaluateFieldDependencies(InputBuilder $field): bool
    {
        $fieldConfig = $field->toArray();
        $dependencies = $fieldConfig['dependencies'] ?? [];

        // No dependencies = always visible
        if (empty($dependencies)) {
            return true;
        }

        $isVisible = false;

        // Check each dependency
        foreach ($dependencies as $dependency) {
            $controllerFieldName = $dependency['field'];
            $requiredValues = $dependency['values'];

            // Get controller field value from form data
            $controllerValue = $this->data[$controllerFieldName] ?? null;

            // Check if dependency is met
            if (in_array($controllerValue, $requiredValues, true)) {
                $isVisible = true;

                // Dispatch FIELD_DEPENDENCY_MET event
                $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_DEPENDENCY_MET, [
                    'trigger_field' => $controllerFieldName,
                    'trigger_value' => $controllerValue,
                    'visible' => true,
                ]);

                break; // At least one dependency is met
            }
        }

        // Dispatch FIELD_DEPENDENCY_CHECK event (allows custom logic)
        $checkEvent = $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_DEPENDENCY_CHECK, [
            'visible' => $isVisible,
        ]);

        // Get final visibility from event (may be modified by listeners)
        $isVisible = $checkEvent->shouldBeVisible();

        // Dispatch show/hide events
        if ($isVisible) {
            $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_SHOW, [
                'visible' => true,
            ]);
        } else {
            $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_HIDE, [
                'visible' => false,
            ]);

            // Dispatch FIELD_DEPENDENCY_NOT_MET event
            $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_DEPENDENCY_NOT_MET, [
                'visible' => false,
            ]);
        }

        return $isVisible;
    }

    /**
     * Get input builder by field name
     *
     * @param string $fieldName Field name
     * @return InputBuilder|null The input builder or null if not found
     */
    public function getInputBuilder(string $fieldName): ?InputBuilder
    {
        foreach ($this->inputs as $item) {
            $input = $item['input'];
            if ($input->getName() === $fieldName) {
                return $input;
            }
        }
        return null;
    }

    /**
     * Trigger field value change event
     *
     * This method simulates a value change and triggers dependent fields.
     * Useful for programmatic value changes that should trigger dependencies.
     *
     * @param string $fieldName Field name that changed
     * @param mixed $newValue New value
     * @param mixed $oldValue Old value (optional)
     */
    public function triggerFieldValueChange(string $fieldName, mixed $newValue, mixed $oldValue = null): void
    {
        $field = $this->getInputBuilder($fieldName);
        if ($field === null) {
            return;
        }

        // Update field value
        $field->value($newValue);

        // Update form data
        $this->data[$fieldName] = $newValue;

        // Dispatch FIELD_VALUE_CHANGE event
        $this->dispatchFieldEvent($field, \FormGenerator\V2\Event\FieldEvents::FIELD_VALUE_CHANGE, [
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);

        // Re-evaluate all dependent fields
        foreach ($this->inputs as $item) {
            $dependentField = $item['input'];
            $this->evaluateFieldDependencies($dependentField);
        }
    }

    /**
     * Enable server-side dependency evaluation
     *
     * When enabled, fields with unmet dependencies will not be rendered
     * in the HTML output (PHP-side conditional rendering).
     *
     * @param bool $enable Enable/disable server-side evaluation
     */
    public function enableServerSideDependencyEvaluation(bool $enable = true): self
    {
        $this->attributes['data-server-side-dependencies'] = $enable ? 'true' : 'false';
        return $this;
    }

    // ========================================================================
    // NESTED FORMS & COLLECTIONS (v2.4.0)
    // ========================================================================

    /**
     * Add a nested form (sub-form)
     *
     * Creates a nested form structure for handling hierarchical data.
     *
     * Example:
     * ```php
     * $form = FormBuilder::create('user')
     *     ->addText('name')->add()
     *
     *     // Nested address form
     *     ->addNestedForm('address', 'Address', function(FormBuilder $addressForm) {
     *         $addressForm->addText('street', 'Street')->add();
     *         $addressForm->addText('city', 'City')->add();
     *         $addressForm->addText('zipcode', 'ZIP')->add();
     *     })
     *
     *     ->buildForm();
     *
     * // Data: ['name' => 'John', 'address' => ['street' => '...', 'city' => '...', 'zipcode' => '...']]
     * ```
     *
     * @param string $name Nested form name
     * @param string|null $label Nested form label
     * @param callable $builder Builder callback that receives FormBuilder for sub-form
     * @return self
     * @since 2.4.0
     */
    public function addNestedForm(string $name, ?string $label = null, callable $builder): self
    {
        // Store nested form configuration
        $this->nestedForms[$name] = [
            'label' => $label ?? $name,
            'builder' => $builder,
        ];

        return $this;
    }

    /**
     * Add a collection field (dynamic list of forms)
     *
     * Creates a collection of forms that can be dynamically added/removed.
     * Similar to Symfony's CollectionType.
     *
     * Example:
     * ```php
     * $form = FormBuilder::create('invoice')
     *     ->addText('invoice_number')->add()
     *
     *     // Collection of line items
     *     ->addCollection('items', 'Line Items', function(FormBuilder $itemForm) {
     *         $itemForm->addText('product', 'Product')->add();
     *         $itemForm->addNumber('quantity', 'Quantity')->add();
     *         $itemForm->addNumber('price', 'Price')->add();
     *     })
     *     ->allowAdd()
     *     ->allowDelete()
     *     ->min(1)
     *     ->max(10)
     *
     *     ->buildForm();
     * ```
     *
     * @param string $name Collection name
     * @param string|null $label Collection label
     * @param callable $prototypeBuilder Builder callback for each collection entry
     * @return CollectionBuilder
     * @since 2.4.0
     */
    public function addCollection(string $name, ?string $label = null, callable $prototypeBuilder): CollectionBuilder
    {
        $collectionBuilder = new CollectionBuilder($this, $name, $label, $prototypeBuilder);
        $this->collections[$name] = $collectionBuilder;
        return $collectionBuilder;
    }

    /**
     * Build and return Form object (v2.4.0)
     *
     * Returns a stateful Form object instead of HTML string.
     * Provides full control over form state, validation, and rendering.
     *
     * Example:
     * ```php
     * $form = FormBuilder::create('user')
     *     ->addText('name')->add()
     *     ->addEmail('email')->add()
     *     ->buildForm();
     *
     * $form->handleRequest($_POST);
     *
     * if ($form->isSubmitted() && $form->isValid()) {
     *     $data = $form->getData();
     *     // Save to database
     * }
     *
     * echo $form->render($renderer, $theme);
     * ```
     *
     * @return FormInterface Stateful form object
     * @since 2.4.0
     */
    public function buildForm(): FormInterface
    {
        // Create form configuration
        $config = new FormConfig(
            name: $this->name,
            type: 'form',
            method: $this->method,
            action: $this->action,
            attributes: $this->attributes,
            compound: true,
            csrfProtection: $this->enableCsrf,
            validation: $this->enableValidation,
        );

        // Create root form
        $form = new Form($this->name, $config);
        $form->setRenderer($this->renderer);
        $form->setTheme($this->theme);
        $form->setValidator($this->validator);

        // Add simple fields
        foreach ($this->inputs as $item) {
            $input = $item['input'];
            $fieldConfig = new FormConfig(
                name: $input->getName(),
                type: $input->getType()->value,
                options: $input->toArray(),
                compound: false
            );

            $field = new Form($input->getName(), $fieldConfig, $input->toArray());
            $form->add($input->getName(), $field);
        }

        // Add nested forms
        foreach ($this->nestedForms as $name => $nestedConfig) {
            $nestedBuilder = new self($name);
            $nestedBuilder->setRenderer($this->renderer);
            $nestedBuilder->setTheme($this->theme);
            $nestedBuilder->setValidator($this->validator);

            // Build nested form using callback
            ($nestedConfig['builder'])($nestedBuilder);

            // Build nested form object
            $nestedForm = $nestedBuilder->buildForm();
            $form->add($name, $nestedForm);
        }

        // Add collections
        foreach ($this->collections as $name => $collectionBuilder) {
            $collectionForm = $collectionBuilder->buildCollectionForm();
            $form->add($name, $collectionForm);
        }

        // Set form data if provided
        if (!empty($this->data)) {
            $mapper = new FormDataMapper();
            $mapper->mapDataToForms($this->data, $form);
        }

        return $form;
    }

    /**
     * Check if form has nested forms or collections
     *
     * @return bool
     * @since 2.4.0
     */
    public function hasNestedStructure(): bool
    {
        return !empty($this->nestedForms) || !empty($this->collections);
    }

    /**
     * Get all nested forms
     *
     * @return array
     * @since 2.4.0
     */
    public function getNestedForms(): array
    {
        return $this->nestedForms;
    }

    /**
     * Get all collections
     *
     * @return array
     * @since 2.4.0
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    /**
     * Get all inputs (for internal use)
     *
     * @return array
     * @since 2.4.0
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    // ========================================================================
    // TYPE SYSTEM (v2.5.0)
    // ========================================================================

    /**
     * Add field using type system (v2.5.0)
     *
     * Create a field using a registered type with options.
     * Supports custom types, built-in types, and type extensions.
     *
     * Example:
     * ```php
     * $form = FormBuilder::create('user')
     *     ->addField('email', 'email', [
     *         'label' => 'Email Address',
     *         'required' => true,
     *         'help' => 'We will never share your email',
     *     ])
     *
     *     ->addField('phone', 'phone', [
     *         'label' => 'Phone Number',
     *         'country' => 'US',
     *     ])
     *
     *     ->buildForm();
     * ```
     *
     * @param string $name Field name
     * @param string $type Type name (registered type or built-in)
     * @param array $options Field options
     * @return InputBuilder
     * @since 2.5.0
     */
    public function addField(string $name, string $type, array $options = []): InputBuilder
    {
        // Ensure built-in types are registered
        TypeRegistry::registerBuiltInTypes();

        // Get type instance
        if (!TypeRegistry::has($type)) {
            throw new \InvalidArgumentException(sprintf(
                'Type "%s" is not registered. Did you forget to register it with TypeRegistry::register()?',
                $type
            ));
        }

        $typeInstance = TypeRegistry::get($type);

        // Build type hierarchy (type + all parents)
        $typeHierarchy = TypeRegistry::getTypeHierarchy($type);

        // Create options resolver
        $resolver = new OptionsResolver();

        // Configure options (starting from root type)
        foreach (array_reverse($typeHierarchy) as $typeInHierarchy) {
            $typeInHierarchy->configureOptions($resolver);

            // Apply type extensions
            $extensions = TypeExtensionRegistry::getExtensionsForType($typeInHierarchy->getName());
            foreach ($extensions as $extension) {
                $extension->configureOptions($resolver);
            }
        }

        // Resolve options
        $resolvedOptions = $resolver->resolve($options);

        // Create input builder (we need to determine InputType from the type)
        // For now, create a generic TEXT type and let the type configure it
        $input = $this->createInput($name, InputType::TEXT, $resolvedOptions['label'] ?? $name);

        // Build field (starting from root type)
        foreach (array_reverse($typeHierarchy) as $typeInHierarchy) {
            $typeInHierarchy->buildField($input, $resolvedOptions);

            // Apply type extensions
            $extensions = TypeExtensionRegistry::getExtensionsForType($typeInHierarchy->getName());
            foreach ($extensions as $extension) {
                $extension->buildField($input, $resolvedOptions);
            }
        }

        // Finish view (starting from root type)
        foreach (array_reverse($typeHierarchy) as $typeInHierarchy) {
            $typeInHierarchy->finishView($input, $resolvedOptions);

            // Apply type extensions
            $extensions = TypeExtensionRegistry::getExtensionsForType($typeInHierarchy->getName());
            foreach ($extensions as $extension) {
                $extension->finishView($input, $resolvedOptions);
            }
        }

        return $input;
    }

    /**
     * Register a custom type
     *
     * Convenience method for registering types
     *
     * @param string $name Type name
     * @param string $className Type class name
     * @since 2.5.0
     */
    public static function registerType(string $name, string $className): void
    {
        TypeRegistry::register($name, $className);
    }

    /**
     * Register a type extension
     *
     * Convenience method for registering extensions
     *
     * @param TypeExtensionInterface $extension Type extension
     * @since 2.5.0
     */
    public static function registerTypeExtension($extension): void
    {
        TypeExtensionRegistry::register($extension);
    }

    /**
     * Check if a type is registered
     *
     * @param string $name Type name
     * @return bool
     * @since 2.5.0
     */
    public static function hasType(string $name): bool
    {
        TypeRegistry::registerBuiltInTypes();
        return TypeRegistry::has($name);
    }

    /**
     * Get all registered type names
     *
     * @return array<string>
     * @since 2.5.0
     */
    public static function getRegisteredTypes(): array
    {
        TypeRegistry::registerBuiltInTypes();
        return TypeRegistry::getTypeNames();
    }

    // ========================================================================
    // CROSS-FIELD VALIDATION & GROUPS (v2.7.0)
    // ========================================================================

    /**
     * Add form-level constraint (v2.7.0)
     *
     * Add a constraint that validates the entire form data.
     * Perfect for cross-field validation.
     *
     * Example:
     * ```php
     * use FormGenerator\V2\Validation\Constraints\Callback;
     *
     * $form = FormBuilder::create('user')
     *     ->addPassword('password')->add()
     *     ->addPassword('password_confirm')->add()
     *
     *     // Cross-field validation
     *     ->addConstraint(new Callback(function($data, $context) {
     *         if ($data['password'] !== $data['password_confirm']) {
     *             $context->buildViolation('Passwords do not match')
     *                     ->atPath('password_confirm')
     *                     ->addViolation();
     *         }
     *     }))
     *
     *     ->buildForm();
     * ```
     *
     * @param object $constraint Constraint instance
     * @return self
     * @since 2.7.0
     */
    public function addConstraint(object $constraint): self
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    /**
     * Get all form-level constraints
     *
     * @return array
     * @since 2.7.0
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Set validation groups for this form (v2.7.0)
     *
     * Define which validation groups to use when validating.
     *
     * Example:
     * ```php
     * $form = FormBuilder::create('user')
     *     ->addText('username')
     *         ->required(['groups' => ['registration', 'profile']])
     *         ->minLength(3, ['groups' => ['registration']])
     *         ->add()
     *
     *     ->setValidationGroups(['registration']) // Only validate registration rules
     *     ->buildForm();
     * ```
     *
     * @param array $groups Validation group names
     * @return self
     * @since 2.7.0
     */
    public function setValidationGroups(array $groups): self
    {
        $this->validationGroups = $groups;
        return $this;
    }

    /**
     * Get validation groups
     *
     * @return array
     * @since 2.7.0
     */
    public function getValidationGroups(): array
    {
        return empty($this->validationGroups) ? ['Default'] : $this->validationGroups;
    }

    /**
     * Validate form data with cross-field validation and groups (v2.7.0)
     *
     * Enhanced validation that supports:
     * - Cross-field validation via constraints
     * - Validation groups
     * - ExecutionContext for better error handling
     *
     * @param array $data Data to validate
     * @param array $groups Validation groups (overrides form groups)
     * @return array Validation errors
     * @since 2.7.0
     */
    public function validateWithConstraints(array $data, array $groups = []): array
    {
        $groups = !empty($groups) ? $groups : $this->getValidationGroups();
        $errors = [];

        // First, validate fields using standard validation
        if ($this->validator !== null) {
            $result = $this->validator->validate($data);
            if (!$result->isValid()) {
                $errors = $result->getErrors();
            }
        }

        // Then, run form-level constraints
        $context = new \FormGenerator\V2\Validation\ExecutionContext($data);

        foreach ($this->constraints as $constraint) {
            // Check if constraint applies to current groups
            if (method_exists($constraint, 'getGroups')) {
                $constraintGroups = $constraint->getGroups();
                if (!array_intersect($groups, $constraintGroups)) {
                    continue; // Skip this constraint
                }
            }

            if (method_exists($constraint, 'validate')) {
                $constraint->validate($data, $context);
            }
        }

        // Merge constraint violations with field errors
        if ($context->hasViolations()) {
            foreach ($context->getViolations() as $path => $messages) {
                if (!isset($errors[$path])) {
                    $errors[$path] = [];
                }
                $errors[$path] = array_merge($errors[$path], $messages);
            }
        }

        return $errors;
    }
}
