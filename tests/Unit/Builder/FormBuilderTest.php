<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Contracts\InputType;
use FormGenerator\V2\Contracts\ScopeType;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use FormGenerator\V2\Theme\TailwindTheme;
use FormGenerator\V2\Validation\NativeValidator;
use FormGenerator\V2\DataProvider\ArrayDataProvider;
use FormGenerator\V2\Security\SecurityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FormBuilder::class)]
class FormBuilderTest extends TestCase
{
    private FormBuilder $formBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formBuilder = new FormBuilder('test-form');
    }

    #[Test]
    public function it_creates_form_with_name(): void
    {
        $html = $this->formBuilder->build();
        
        $this->assertIsHtml($html);
        $this->assertHtmlContainsTag($html, 'form');
        $this->assertStringContainsString('test-form', $html);
    }

    #[Test]
    public function it_sets_form_action(): void
    {
        $html = $this->formBuilder
            ->setAction('/submit')
            ->build();
        
        $this->assertHtmlContainsAttribute($html, 'action', '/submit');
    }

    #[Test]
    public function it_sets_form_method(): void
    {
        $html = $this->formBuilder
            ->setMethod('GET')
            ->build();
        
        $this->assertHtmlContainsAttribute($html, 'method', 'get');
    }

    #[Test]
    public function it_sets_form_classes(): void
    {
        $html = $this->formBuilder
            ->setClasses(['form-horizontal', 'my-form'])
            ->build();
        
        $this->assertStringContainsString('form-horizontal', $html);
        $this->assertStringContainsString('my-form', $html);
    }

    #[Test]
    public function it_adds_form_attributes(): void
    {
        $html = $this->formBuilder
            ->setAttributes([
                'data-test' => 'value',
                'novalidate' => ''
            ])
            ->build();
        
        $this->assertStringContainsString('data-test="value"', $html);
        $this->assertStringContainsString('novalidate', $html);
    }

    #[Test]
    public function it_enables_csrf_protection(): void
    {
        $security = new SecurityManager();
        $html = $this->formBuilder
            ->enableCsrf($security)
            ->build();
        
        $this->assertStringContainsString('_csrf_token', $html);
        $this->assertHtmlContainsTag($html, 'input');
    }

    #[Test]
    public function it_adds_text_input(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->add()
            ->build();
        
        $this->assertStringContainsString('username', $html);
        $this->assertStringContainsString('Username', $html);
        $this->assertStringContainsString('type="text"', $html);
    }

    #[Test]
    public function it_adds_email_input(): void
    {
        $html = $this->formBuilder
            ->addEmail('email', 'Email Address')
            ->add()
            ->build();
        
        $this->assertStringContainsString('email', $html);
        $this->assertStringContainsString('type="email"', $html);
    }

    #[Test]
    public function it_adds_password_input(): void
    {
        $html = $this->formBuilder
            ->addPassword('password', 'Password')
            ->add()
            ->build();
        
        $this->assertStringContainsString('password', $html);
        $this->assertStringContainsString('type="password"', $html);
    }

    #[Test]
    public function it_adds_textarea(): void
    {
        $html = $this->formBuilder
            ->addTextarea('description', 'Description')
            ->add()
            ->build();
        
        $this->assertStringContainsString('description', $html);
        $this->assertHtmlContainsTag($html, 'textarea');
    }

    #[Test]
    public function it_adds_select_with_options(): void
    {
        $html = $this->formBuilder
            ->addSelect('country', 'Country')
            ->options([
                'us' => 'United States',
                'uk' => 'United Kingdom',
                'ca' => 'Canada'
            ])
            ->add()
            ->build();
        
        $this->assertStringContainsString('country', $html);
        $this->assertHtmlContainsTag($html, 'select');
        $this->assertStringContainsString('United States', $html);
        $this->assertStringContainsString('United Kingdom', $html);
    }

    #[Test]
    public function it_adds_checkbox(): void
    {
        $html = $this->formBuilder
            ->addCheckbox('terms', 'I agree to terms')
            ->add()
            ->build();
        
        $this->assertStringContainsString('terms', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
    }

    #[Test]
    public function it_adds_radio_buttons(): void
    {
        $html = $this->formBuilder
            ->addRadio('gender', 'Gender')
            ->options([
                'male' => 'Male',
                'female' => 'Female'
            ])
            ->add()
            ->build();
        
        $this->assertStringContainsString('gender', $html);
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('Male', $html);
        $this->assertStringContainsString('Female', $html);
    }

    #[Test]
    public function it_adds_hidden_input(): void
    {
        $html = $this->formBuilder
            ->addHidden('user_id', '123')
            ->add()
            ->build();
        
        $this->assertStringContainsString('user_id', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('value="123"', $html);
    }

    #[Test]
    public function it_adds_submit_button(): void
    {
        $html = $this->formBuilder
            ->addSubmit('Save')
            ->build();
        
        $this->assertStringContainsString('Save', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    #[Test]
    public function it_marks_field_as_required(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->required()
            ->add()
            ->build();
        
        $this->assertStringContainsString('required', $html);
    }

    #[Test]
    public function it_sets_field_value(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->value('john_doe')
            ->add()
            ->build();
        
        $this->assertStringContainsString('value="john_doe"', $html);
    }

    #[Test]
    public function it_sets_field_placeholder(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->placeholder('Enter your username')
            ->add()
            ->build();
        
        $this->assertStringContainsString('placeholder="Enter your username"', $html);
    }

    #[Test]
    public function it_sets_field_help_text(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->helpText('Must be 3-20 characters')
            ->add()
            ->build();
        
        $this->assertStringContainsString('Must be 3-20 characters', $html);
    }

    #[Test]
    public function it_chains_multiple_methods(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->required()
            ->placeholder('Enter username')
            ->helpText('3-20 characters')
            ->value('john')
            ->add()
            ->addEmail('email', 'Email')
            ->required()
            ->add()
            ->addSubmit('Register')
            ->build();
        
        $this->assertStringContainsString('username', $html);
        $this->assertStringContainsString('email', $html);
        $this->assertStringContainsString('Register', $html);
    }

    #[Test]
    public function it_uses_bootstrap5_theme(): void
    {
        $theme = new Bootstrap5Theme();
        $html = $this->formBuilder
            ->setTheme($theme)
            ->addText('test', 'Test')
            ->add()
            ->build();
        
        $this->assertStringContainsString('form-control', $html);
        $this->assertStringContainsString('form-label', $html);
    }

    #[Test]
    public function it_uses_tailwind_theme(): void
    {
        $theme = new TailwindTheme();
        $html = $this->formBuilder
            ->setTheme($theme)
            ->addText('test', 'Test')
            ->add()
            ->build();
        
        $this->assertStringContainsString('rounded', $html);
        $this->assertStringContainsString('border', $html);
    }

    #[Test]
    public function it_sets_validator(): void
    {
        $validator = new NativeValidator();
        $result = $this->formBuilder->setValidator($validator);
        
        $this->assertInstanceOf(FormBuilder::class, $result);
    }

    #[Test]
    public function it_generates_validation_javascript(): void
    {
        $validator = new NativeValidator();
        $html = $this->formBuilder
            ->setValidator($validator)
            ->addText('email', 'Email')
            ->addValidation('email', 'required')
            ->addValidation('email', 'email')
            ->add()
            ->build();
        
        $this->assertStringContainsString('<script>', $html);
        $this->assertStringContainsString('FormValidator_', $html);
    }

    #[Test]
    public function it_populates_form_with_data(): void
    {
        $data = [
            'username' => 'john_doe',
            'email' => 'john@example.com'
        ];
        
        $html = $this->formBuilder
            ->addText('username', 'Username')->add()
            ->addEmail('email', 'Email')->add()
            ->populate($data)
            ->build();
        
        $this->assertStringContainsString('value="john_doe"', $html);
        $this->assertStringContainsString('value="john@example.com"', $html);
    }

    #[Test]
    public function it_uses_data_provider(): void
    {
        $dataProvider = new ArrayDataProvider([
            ['id' => 1, 'name' => 'Option 1'],
            ['id' => 2, 'name' => 'Option 2']
        ]);
        
        $html = $this->formBuilder
            ->addSelect('option', 'Select Option')
            ->optionsFromProvider($dataProvider, 'id', 'name')
            ->add()
            ->build();
        
        $this->assertStringContainsString('Option 1', $html);
        $this->assertStringContainsString('Option 2', $html);
    }

    #[Test]
    public function it_disables_validation(): void
    {
        $validator = new NativeValidator();
        $html = $this->formBuilder
            ->setValidator($validator)
            ->enableValidation(false)
            ->addText('email', 'Email')
            ->addValidation('email', 'required')
            ->add()
            ->build();
        
        // Should not contain validation JavaScript
        $this->assertStringNotContainsString('FormValidator_', $html);
    }

    #[Test]
    public function it_disables_client_side_validation_only(): void
    {
        $validator = new NativeValidator();
        $html = $this->formBuilder
            ->setValidator($validator)
            ->enableClientSideValidation(false)
            ->addText('email', 'Email')
            ->addValidation('email', 'required')
            ->add()
            ->build();
        
        // Should not contain client-side validation JavaScript
        $this->assertStringNotContainsString('FormValidator_', $html);
    }

    #[Test]
    public function it_handles_multipart_form_data(): void
    {
        $html = $this->formBuilder
            ->addFile('avatar', 'Avatar')
            ->add()
            ->build();
        
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
    }

    #[Test]
    public function it_returns_form_builder_from_chain(): void
    {
        $result = $this->formBuilder
            ->addText('test', 'Test')
            ->required()
            ->add();
        
        $this->assertInstanceOf(FormBuilder::class, $result);
    }

    #[Test]
    public function it_generates_unique_form_id(): void
    {
        $form1 = new FormBuilder('form1');
        $form2 = new FormBuilder('form2');
        
        $html1 = $form1->build();
        $html2 = $form2->build();
        
        $this->assertStringContainsString('form1', $html1);
        $this->assertStringContainsString('form2', $html2);
        $this->assertStringNotContainsString('form2', $html1);
        $this->assertStringNotContainsString('form1', $html2);
    }
}
