<?php

declare(strict_types=1);

namespace FormGenerator\V2\Form;

use FormGenerator\V2\Contracts\{RendererInterface, ThemeInterface, ValidatorInterface};
use FormGenerator\V2\Validation\ValidationManager;
use FormGenerator\V2\Event\{EventDispatcher, FormEvent, FormEvents};
use FormGenerator\V2\Error\{ErrorList, FormError, ErrorLevel, ErrorBubblingStrategy};

/**
 * Form - Stateful Form Object with Nested Support
 *
 * Core form class that manages form state, validation, children, and rendering.
 * Supports nested forms and collections for complex data structures.
 *
 * Usage:
 * ```php
 * $form = new Form('user_form', $config);
 * $form->add('name', 'text', ['label' => 'Name', 'required' => true]);
 * $form->add('email', 'email', ['label' => 'Email']);
 *
 * $form->handleRequest($_POST);
 *
 * if ($form->isSubmitted() && $form->isValid()) {
 *     $data = $form->getData();
 *     // Save to database
 * }
 * ```
 *
 * @author selcukmart
 * @since 2.4.0
 */
class Form implements FormInterface
{
    /**
     * Form configuration
     */
    private readonly FormConfigInterface $config;

    /**
     * Form state
     */
    private FormState $state = FormState::BUILDING;

    /**
     * Form data (model data)
     */
    private array $data = [];

    /**
     * Submitted data (view data)
     */
    private array $submittedData = [];

    /**
     * Child forms/fields
     *
     * @var array<string, FormInterface>
     */
    private array $children = [];

    /**
     * Parent form (null for root forms)
     */
    private ?FormInterface $parent = null;

    /**
     * Validation errors (legacy array format)
     */
    private array $errors = [];

    /**
     * Structured error list (v2.9.0)
     */
    private ErrorList $errorList;

    /**
     * Error bubbling strategy (v2.9.0)
     */
    private ErrorBubblingStrategy $errorBubblingStrategy;

    /**
     * Validator instance
     */
    private ?ValidatorInterface $validator = null;

    /**
     * Renderer instance
     */
    private ?RendererInterface $renderer = null;

    /**
     * Theme instance
     */
    private ?ThemeInterface $theme = null;

    /**
     * Field metadata (from InputBuilder)
     */
    private array $metadata = [];

    /**
     * Event dispatcher (v2.8.0)
     */
    private EventDispatcher $eventDispatcher;

    public function __construct(
        string $name,
        ?FormConfigInterface $config = null,
        array $metadata = []
    ) {
        $this->config = $config ?? new FormConfig(
            name: $name,
            type: 'form',
            compound: true
        );
        $this->metadata = $metadata;
        $this->state = FormState::READY;
        $this->eventDispatcher = new EventDispatcher();
        $this->errorList = new ErrorList();
        $this->errorBubblingStrategy = ErrorBubblingStrategy::enabled();
    }

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function setData(array $data): self
    {
        // Dispatch PRE_SET_DATA event
        $preEvent = new FormEvent($this, $data);
        $this->eventDispatcher->dispatch(FormEvents::PRE_SET_DATA, $preEvent);

        // Use potentially modified data from event
        $data = $preEvent->getData();

        $this->data = $data;

        // Set data on children recursively
        foreach ($this->children as $name => $child) {
            if (isset($data[$name])) {
                if ($child->getConfig()->isCompound()) {
                    // Nested form or collection
                    $child->setData(is_array($data[$name]) ? $data[$name] : []);
                } else {
                    // Simple field
                    $child->setData(['value' => $data[$name]]);
                }
            }
        }

        // Dispatch POST_SET_DATA event
        $postEvent = new FormEvent($this, $this->data);
        $this->eventDispatcher->dispatch(FormEvents::POST_SET_DATA, $postEvent);

        return $this;
    }

    public function getData(): array
    {
        // If submitted, return submitted data; otherwise return bound data
        if ($this->isSubmitted()) {
            return $this->gatherDataFromChildren();
        }

        return $this->data;
    }

    /**
     * Gather data from all children recursively
     */
    private function gatherDataFromChildren(): array
    {
        $data = [];

        foreach ($this->children as $name => $child) {
            if ($child->getConfig()->isCompound()) {
                // Nested form or collection
                $data[$name] = $child->getData();
            } else {
                // Simple field
                $childData = $child->getData();
                $data[$name] = $childData['value'] ?? null;
            }
        }

        return $data;
    }

    public function handleRequest(array $data): self
    {
        // Check if form was actually submitted (form name present in data)
        $formName = $this->getName();

        if (isset($data[$formName]) || $this->isRoot()) {
            $submittedData = $this->isRoot() ? $data : ($data[$formName] ?? []);
            return $this->submit($submittedData);
        }

        return $this;
    }

    public function submit(array $data): self
    {
        // Dispatch PRE_SUBMIT event
        $preEvent = new FormEvent($this, $data);
        $this->eventDispatcher->dispatch(FormEvents::PRE_SUBMIT, $preEvent);

        // Use potentially modified data from event
        $data = $preEvent->getData();

        $this->submittedData = $data;
        $this->state = FormState::SUBMITTED;

        // Submit to children
        foreach ($this->children as $name => $child) {
            if (isset($data[$name])) {
                $child->submit(is_array($data[$name]) ? $data[$name] : ['value' => $data[$name]]);
            }
        }

        // Validate after submission
        $this->errors = $this->validate();
        $this->state = empty($this->errors) ? FormState::VALID : FormState::INVALID;

        // Dispatch POST_SUBMIT event
        $postEvent = new FormEvent($this, $this->getData());
        $this->eventDispatcher->dispatch(FormEvents::POST_SUBMIT, $postEvent);

        return $this;
    }

    public function isSubmitted(): bool
    {
        return $this->state->isSubmitted();
    }

    public function isValid(): bool
    {
        return $this->state->isValid();
    }

    public function isEmpty(): bool
    {
        return empty($this->data) && empty($this->submittedData);
    }

    public function validate(): array
    {
        $errors = [];

        // Validate children first
        foreach ($this->children as $name => $child) {
            $childErrors = $child->validate();
            if (!empty($childErrors)) {
                $errors[$name] = $childErrors;
            }
        }

        // Validate this form if validator is set
        if ($this->validator !== null) {
            $data = $this->isSubmitted() ? $this->submittedData : $this->data;
            $validationResult = $this->validator->validate($data);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }

        $this->errors = $errors;
        return $errors;
    }

    public function getErrors(bool $deep = false): array
    {
        if (!$deep) {
            return $this->errors;
        }

        // Get errors from children recursively
        $allErrors = $this->errors;

        foreach ($this->children as $name => $child) {
            $childErrors = $child->getErrors(true);
            if (!empty($childErrors)) {
                $allErrors[$name] = $childErrors;
            }
        }

        return $allErrors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get structured error list (v2.9.0)
     *
     * @param bool $deep Include errors from children
     * @return ErrorList Structured error collection
     */
    public function getErrorList(bool $deep = false): ErrorList
    {
        if (!$deep) {
            return $this->errorList;
        }

        // Collect errors from all children with bubbling
        $allErrors = clone $this->errorList;

        foreach ($this->children as $name => $child) {
            if ($this->errorBubblingStrategy->isEnabled()) {
                $bubbledErrors = $this->errorBubblingStrategy->collectErrors($this, $child);
                $allErrors = $allErrors->merge($bubbledErrors);
            }
        }

        return $allErrors;
    }

    /**
     * Get errors as nested array (v2.9.0)
     *
     * Returns errors in nested array format:
     * [
     *   'email' => ['Email is required'],
     *   'address' => [
     *     'street' => ['Street is required'],
     *     'zipcode' => ['Invalid ZIP']
     *   ]
     * ]
     */
    public function getErrorsAsArray(bool $deep = false): array
    {
        return $this->getErrorList($deep)->toArray();
    }

    /**
     * Get errors as flat array with dot notation (v2.9.0)
     *
     * Returns errors in flat format:
     * [
     *   'email' => 'Email is required',
     *   'address.street' => 'Street is required',
     *   'address.zipcode' => 'Invalid ZIP'
     * ]
     */
    public function getErrorsFlattened(bool $deep = false): array
    {
        return $this->getErrorList($deep)->toFlat();
    }

    /**
     * Add error to form (v2.9.0)
     *
     * @param string $message Error message
     * @param ErrorLevel $level Error severity
     * @param string|null $path Field path
     * @param array $parameters Message parameters
     */
    public function addError(
        string $message,
        ErrorLevel $level = ErrorLevel::ERROR,
        ?string $path = null,
        array $parameters = []
    ): self {
        $error = new FormError(
            message: $message,
            level: $level,
            path: $path,
            parameters: $parameters,
            origin: $this
        );

        $this->errorList->add($error);

        // Also add to legacy errors array for backward compatibility
        if ($path) {
            $this->errors[$path][] = $message;
        } else {
            $this->errors['_form'][] = $message;
        }

        return $this;
    }

    /**
     * Set error bubbling strategy (v2.9.0)
     */
    public function setErrorBubblingStrategy(ErrorBubblingStrategy $strategy): self
    {
        $this->errorBubblingStrategy = $strategy;
        return $this;
    }

    /**
     * Get error bubbling strategy (v2.9.0)
     */
    public function getErrorBubblingStrategy(): ErrorBubblingStrategy
    {
        return $this->errorBubblingStrategy;
    }

    public function add(string $name, string|FormInterface $type = 'text', array $options = []): self
    {
        // If $type is already a Form instance, add it directly
        if ($type instanceof FormInterface) {
            $this->children[$name] = $type;
            $type->setParent($this);
            return $this;
        }

        // Otherwise, create a new field form
        $config = new FormConfig(
            name: $name,
            type: $type,
            options: $options,
            compound: false
        );

        $field = new self($name, $config);
        $field->setParent($this);
        $this->children[$name] = $field;

        return $this;
    }

    public function remove(string $name): self
    {
        if (isset($this->children[$name])) {
            $this->children[$name]->setParent(null);
            unset($this->children[$name]);
        }

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->children[$name]);
    }

    public function get(string $name): FormInterface
    {
        if (!isset($this->children[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Child "%s" does not exist in form "%s"',
                $name,
                $this->getName()
            ));
        }

        return $this->children[$name];
    }

    public function all(): array
    {
        return $this->children;
    }

    public function getParent(): ?FormInterface
    {
        return $this->parent;
    }

    public function setParent(?FormInterface $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getRoot(): FormInterface
    {
        $form = $this;
        while ($form->parent !== null) {
            $form = $form->parent;
        }
        return $form;
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function getConfig(): FormConfigInterface
    {
        return $this->config;
    }

    public function setValidator(?ValidatorInterface $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    public function setRenderer(?RendererInterface $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    public function setTheme(?ThemeInterface $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function createView(): FormView
    {
        $view = new FormView($this->parent?->createView());

        // Set view variables from metadata and form state
        $view->setVars([
            'name' => $this->getName(),
            'value' => $this->getData(),
            'data' => $this->getData(),
            'label' => $this->metadata['label'] ?? $this->getName(),
            'required' => $this->metadata['required'] ?? false,
            'disabled' => $this->metadata['disabled'] ?? false,
            'attr' => $this->metadata['attributes'] ?? [],
            'errors' => $this->getErrors(false),
            'valid' => $this->isValid(),
            'submitted' => $this->isSubmitted(),
            'compound' => $this->config->isCompound(),
            'method' => $this->config->getMethod(),
            'action' => $this->config->getAction(),
        ]);

        // Create views for children
        foreach ($this->children as $name => $child) {
            $view->addChild($name, $child->createView());
        }

        return $view;
    }

    public function render(?RendererInterface $renderer = null, ?ThemeInterface $theme = null): string
    {
        $renderer = $renderer ?? $this->renderer;
        $theme = $theme ?? $this->theme;

        if ($renderer === null) {
            throw new \RuntimeException('No renderer set for form. Call setRenderer() or pass renderer to render().');
        }

        $view = $this->createView();

        return $renderer->render($view, $theme);
    }

    /**
     * Magic method for string conversion
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            return sprintf('Error rendering form: %s', $e->getMessage());
        }
    }

    /**
     * Get form state
     */
    public function getState(): FormState
    {
        return $this->state;
    }

    /**
     * Add event listener (v2.8.0)
     *
     * @param string $eventName Event name (use FormEvents constants)
     * @param callable $listener Listener callable
     * @param int $priority Priority (higher = earlier execution)
     * @return self
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): self
    {
        $this->eventDispatcher->addEventListener($eventName, $listener, $priority);
        return $this;
    }

    /**
     * Get event dispatcher (v2.8.0)
     *
     * @return EventDispatcher Event dispatcher instance
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }
}
