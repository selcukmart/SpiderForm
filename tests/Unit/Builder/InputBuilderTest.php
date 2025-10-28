<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\Builder\InputBuilder;
use FormGenerator\V2\Contracts\InputType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InputBuilder::class)]
class InputBuilderTest extends TestCase
{
    private FormBuilder $formBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formBuilder = new FormBuilder('test-form');
    }

    #[Test]
    public function it_creates_input_with_type_and_name(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->add()
            ->build();
        
        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('type="text"', $html);
    }

    #[Test]
    public function it_sets_required_attribute(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->required()
            ->add()
            ->build();
        
        $this->assertStringContainsString('required', $html);
    }

    #[Test]
    public function it_sets_value(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->value('john_doe')
            ->add()
            ->build();
        
        $this->assertStringContainsString('value="john_doe"', $html);
    }

    #[Test]
    public function it_sets_placeholder(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->placeholder('Enter your username')
            ->add()
            ->build();
        
        $this->assertStringContainsString('placeholder="Enter your username"', $html);
    }

    #[Test]
    public function it_sets_help_text(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->helpText('3-20 characters')
            ->add()
            ->build();
        
        $this->assertStringContainsString('3-20 characters', $html);
    }

    #[Test]
    public function it_sets_custom_attributes(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->attributes([
                'data-test' => 'value',
                'autocomplete' => 'off'
            ])
            ->add()
            ->build();
        
        $this->assertStringContainsString('data-test="value"', $html);
        $this->assertStringContainsString('autocomplete="off"', $html);
    }

    #[Test]
    public function it_sets_custom_classes(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->classes(['custom-input', 'highlighted'])
            ->add()
            ->build();
        
        $this->assertStringContainsString('custom-input', $html);
        $this->assertStringContainsString('highlighted', $html);
    }

    #[Test]
    public function it_adds_dependency(): void
    {
        $html = $this->formBuilder
            ->addRadio('type', 'Type')
            ->options(['individual' => 'Individual', 'company' => 'Company'])
            ->isDependency()
            ->add()
            ->addText('company_name', 'Company Name')
            ->dependsOn('type', 'company')
            ->add()
            ->build();
        
        $this->assertStringContainsString('data-dependency', $html);
        $this->assertStringContainsString('data-dependends', $html);
        $this->assertStringContainsString('data-dependend', $html);
    }

    #[Test]
    public function it_handles_multiple_dependency_values(): void
    {
        $html = $this->formBuilder
            ->addSelect('status', 'Status')
            ->options(['active' => 'Active', 'pending' => 'Pending', 'inactive' => 'Inactive'])
            ->isDependency()
            ->add()
            ->addText('reason', 'Reason')
            ->dependsOn('status', ['pending', 'inactive'])
            ->add()
            ->build();
        
        $this->assertStringContainsString('data-dependend="status-pending status-inactive"', $html);
    }

    #[Test]
    public function it_sets_min_max_for_number(): void
    {
        $html = $this->formBuilder
            ->addNumber('age', 'Age')
            ->min(18)
            ->max(100)
            ->add()
            ->build();
        
        $this->assertStringContainsString('min="18"', $html);
        $this->assertStringContainsString('max="100"', $html);
    }

    #[Test]
    public function it_sets_min_max_length_for_text(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->minLength(3)
            ->maxLength(20)
            ->add()
            ->build();
        
        $this->assertStringContainsString('minlength="3"', $html);
        $this->assertStringContainsString('maxlength="20"', $html);
    }

    #[Test]
    public function it_sets_pattern(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->pattern('[a-zA-Z0-9_]+')
            ->add()
            ->build();
        
        $this->assertStringContainsString('pattern="[a-zA-Z0-9_]+"', $html);
    }

    #[Test]
    public function it_sets_disabled(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->disabled()
            ->add()
            ->build();
        
        $this->assertStringContainsString('disabled', $html);
    }

    #[Test]
    public function it_sets_readonly(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->readonly()
            ->add()
            ->build();
        
        $this->assertStringContainsString('readonly', $html);
    }

    #[Test]
    public function it_adds_validation_rules(): void
    {
        $html = $this->formBuilder
            ->addEmail('email', 'Email')
            ->addValidation('email', 'required')
            ->addValidation('email', 'email')
            ->add()
            ->build();
        
        $this->assertStringContainsString('email', $html);
    }

    #[Test]
    public function it_handles_select_options(): void
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
        
        $this->assertStringContainsString('United States', $html);
        $this->assertStringContainsString('United Kingdom', $html);
        $this->assertStringContainsString('Canada', $html);
        $this->assertStringContainsString('value="us"', $html);
        $this->assertStringContainsString('value="uk"', $html);
    }

    #[Test]
    public function it_handles_selected_option(): void
    {
        $html = $this->formBuilder
            ->addSelect('country', 'Country')
            ->options([
                'us' => 'United States',
                'uk' => 'United Kingdom'
            ])
            ->value('uk')
            ->add()
            ->build();
        
        $this->assertMatchesRegularExpression('/<option[^>]*value="uk"[^>]*selected/', $html);
    }

    #[Test]
    public function it_handles_checked_checkbox(): void
    {
        $html = $this->formBuilder
            ->addCheckbox('terms', 'I agree')
            ->value('1')
            ->checked()
            ->add()
            ->build();
        
        $this->assertStringContainsString('checked', $html);
    }

    #[Test]
    public function it_handles_textarea_rows_cols(): void
    {
        $html = $this->formBuilder
            ->addTextarea('description', 'Description')
            ->rows(5)
            ->cols(40)
            ->add()
            ->build();
        
        $this->assertStringContainsString('rows="5"', $html);
        $this->assertStringContainsString('cols="40"', $html);
    }

    #[Test]
    public function it_returns_form_builder_on_add(): void
    {
        $result = $this->formBuilder
            ->addText('test', 'Test')
            ->add();
        
        $this->assertInstanceOf(FormBuilder::class, $result);
    }

    #[Test]
    public function it_chains_multiple_configurations(): void
    {
        $html = $this->formBuilder
            ->addText('username', 'Username')
            ->required()
            ->placeholder('Enter username')
            ->minLength(3)
            ->maxLength(20)
            ->pattern('[a-zA-Z0-9_]+')
            ->helpText('Alphanumeric and underscore only')
            ->add()
            ->build();
        
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('placeholder="Enter username"', $html);
        $this->assertStringContainsString('minlength="3"', $html);
        $this->assertStringContainsString('maxlength="20"', $html);
        $this->assertStringContainsString('pattern=', $html);
        $this->assertStringContainsString('Alphanumeric and underscore only', $html);
    }

    #[Test]
    public function it_handles_file_input_accept(): void
    {
        $html = $this->formBuilder
            ->addFile('avatar', 'Avatar')
            ->accept('image/png,image/jpeg')
            ->add()
            ->build();
        
        $this->assertStringContainsString('accept="image/png,image/jpeg"', $html);
    }

    #[Test]
    public function it_handles_multiple_file_upload(): void
    {
        $html = $this->formBuilder
            ->addFile('photos', 'Photos')
            ->multiple()
            ->add()
            ->build();
        
        $this->assertStringContainsString('multiple', $html);
    }
}
