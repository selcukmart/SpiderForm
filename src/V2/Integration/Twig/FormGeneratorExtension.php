<?php

declare(strict_types=1);

namespace FormGenerator\V2\Integration\Twig;

use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Contracts\ThemeInterface;
use FormGenerator\V2\Contracts\RendererInterface;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig Extension for FormGenerator V2
 * 
 * Allows form generation directly in Twig templates without controller involvement.
 * 
 * Usage in Twig:
 * {{ form_start('my-form', {'action': '/submit', 'method': 'POST'}) }}
 * {{ form_text('username', 'Username', {'required': true}) }}
 * {{ form_email('email', 'Email') }}
 * {{ form_submit('Save') }}
 * {{ form_end() }}
 *
 * @author selcukmart
 * @since 2.0.0
 */
class FormGeneratorExtension extends AbstractExtension
{
    private ?FormBuilder $currentForm = null;
    private ThemeInterface $defaultTheme;
    private RendererInterface $renderer;

    public function __construct(
        RendererInterface $renderer,
        ?ThemeInterface $defaultTheme = null
    ) {
        $this->renderer = $renderer;
        $this->defaultTheme = $defaultTheme ?? new Bootstrap5Theme();
    }

    public function getFunctions(): array
    {
        return [
            // Form control
            new TwigFunction('form_start', [$this, 'formStart'], ['is_safe' => ['html']]),
            new TwigFunction('form_end', [$this, 'formEnd'], ['is_safe' => ['html']]),
            
            // Input types
            new TwigFunction('form_text', [$this, 'formText']),
            new TwigFunction('form_email', [$this, 'formEmail']),
            new TwigFunction('form_password', [$this, 'formPassword']),
            new TwigFunction('form_textarea', [$this, 'formTextarea']),
            new TwigFunction('form_number', [$this, 'formNumber']),
            new TwigFunction('form_date', [$this, 'formDate']),
            new TwigFunction('form_select', [$this, 'formSelect']),
            new TwigFunction('form_checkbox', [$this, 'formCheckbox']),
            
            // Buttons
            new TwigFunction('form_submit', [$this, 'formSubmit']),
        ];
    }

    public function formStart(string $name, array $options = []): string
    {
        $this->currentForm = FormBuilder::create($name)
            ->setRenderer($this->renderer)
            ->setTheme($this->defaultTheme);

        if (isset($options['action'])) {
            $this->currentForm->setAction($options['action']);
        }

        if (isset($options['method'])) {
            $this->currentForm->setMethod($options['method']);
        }

        return '';
    }

    public function formEnd(): string
    {
        if ($this->currentForm === null) {
            throw new \RuntimeException('No form started');
        }

        $html = $this->currentForm->build();
        $this->currentForm = null;
        return $html;
    }

    public function formText(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addText($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formEmail(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addEmail($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formPassword(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addPassword($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formTextarea(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addTextarea($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formNumber(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addNumber($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formDate(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addDate($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formSelect(string $name, ?string $label = null, array $selectOptions = [], array $options = []): self
    {
        $input = $this->currentForm->addSelect($name, $label);
        if (!empty($selectOptions)) {
            $input->options($selectOptions);
        }
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formCheckbox(string $name, ?string $label = null, array $options = []): self
    {
        $input = $this->currentForm->addCheckbox($name, $label);
        $this->applyOptions($input, $options);
        $input->add();
        return $this;
    }

    public function formSubmit(string $label = 'Submit'): self
    {
        $this->currentForm->addSubmit('submit', $label);
        return $this;
    }

    private function applyOptions($input, array $options): void
    {
        if (isset($options['required']) && $options['required']) {
            $input->required();
        }

        if (isset($options['placeholder'])) {
            $input->placeholder($options['placeholder']);
        }

        if (isset($options['help'])) {
            $input->helpText($options['help']);
        }

        if (isset($options['value'])) {
            $input->value($options['value']);
        }
    }

    public function getName(): string
    {
        return 'formgenerator';
    }
}
