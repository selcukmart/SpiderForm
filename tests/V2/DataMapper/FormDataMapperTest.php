<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\DataMapper;

use FormGenerator\V2\DataMapper\FormDataMapper;
use FormGenerator\V2\Form\Form;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FormDataMapper
 *
 * @covers \FormGenerator\V2\DataMapper\FormDataMapper
 */
class FormDataMapperTest extends TestCase
{
    private FormDataMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new FormDataMapper();
    }

    public function testMapDataToFormsSimpleFields(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');
        $form->add('email', 'email');

        $data = ['name' => 'John', 'email' => 'john@example.com'];

        $this->mapper->mapDataToForms($data, $form);

        $formData = $form->getData();
        $this->assertEquals('John', $formData['name']);
        $this->assertEquals('john@example.com', $formData['email']);
    }

    public function testMapDataToFormsNestedForms(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');

        $addressForm = new Form('address');
        $addressForm->add('street', 'text');
        $addressForm->add('city', 'text');
        $form->add('address', $addressForm);

        $data = [
            'name' => 'John',
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York'
            ]
        ];

        $this->mapper->mapDataToForms($data, $form);

        $formData = $form->getData();
        $this->assertEquals('John', $formData['name']);
        $this->assertEquals('123 Main St', $formData['address']['street']);
        $this->assertEquals('New York', $formData['address']['city']);
    }

    public function testMapFormsToDataSimpleFields(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');
        $form->add('email', 'email');

        $form->submit(['name' => 'Jane', 'email' => 'jane@example.com']);

        $data = $this->mapper->mapFormsToData($form);

        $this->assertEquals('Jane', $data['name']);
        $this->assertEquals('jane@example.com', $data['email']);
    }

    public function testMapFormsToDataNestedForms(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');

        $addressForm = new Form('address');
        $addressForm->add('street', 'text');
        $addressForm->add('city', 'text');
        $form->add('address', $addressForm);

        $form->submit([
            'name' => 'Jane',
            'address' => [
                'street' => '456 Oak Ave',
                'city' => 'Boston'
            ]
        ]);

        $data = $this->mapper->mapFormsToData($form);

        $this->assertEquals('Jane', $data['name']);
        $this->assertEquals('456 Oak Ave', $data['address']['street']);
        $this->assertEquals('Boston', $data['address']['city']);
    }

    public function testMapObjectToFormsConvertsObjectToArray(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');
        $form->add('email', 'email');

        $object = (object) ['name' => 'Mike', 'email' => 'mike@example.com'];

        $this->mapper->mapObjectToForms($object, $form);

        $data = $form->getData();
        $this->assertEquals('Mike', $data['name']);
        $this->assertEquals('mike@example.com', $data['email']);
    }

    public function testMapObjectToFormsHandlesNestedObjects(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');

        $addressForm = new Form('address');
        $addressForm->add('city', 'text');
        $form->add('address', $addressForm);

        $address = (object) ['city' => 'Chicago'];
        $user = (object) ['name' => 'Sarah', 'address' => $address];

        $this->mapper->mapObjectToForms($user, $form);

        $data = $form->getData();
        $this->assertEquals('Sarah', $data['name']);
        $this->assertEquals('Chicago', $data['address']['city']);
    }

    public function testFlattenErrorsSimpleErrors(): void
    {
        $errors = [
            'name' => ['Name is required'],
            'email' => ['Invalid email']
        ];

        $flattened = $this->mapper->flattenErrors($errors);

        $this->assertEquals('Name is required', $flattened['name']);
        $this->assertEquals('Invalid email', $flattened['email']);
    }

    public function testFlattenErrorsNestedErrors(): void
    {
        $errors = [
            'name' => ['Name is required'],
            'address' => [
                'street' => ['Street is required'],
                'city' => ['City is required']
            ]
        ];

        $flattened = $this->mapper->flattenErrors($errors);

        $this->assertEquals('Name is required', $flattened['name']);
        $this->assertEquals('Street is required', $flattened['address.street']);
        $this->assertEquals('City is required', $flattened['address.city']);
    }

    public function testFlattenErrorsHandlesMultipleErrorsPerField(): void
    {
        $errors = [
            'password' => [
                'Password is required',
                'Password must be at least 8 characters'
            ]
        ];

        $flattened = $this->mapper->flattenErrors($errors);

        $this->assertStringContainsString('Password is required', $flattened['password']);
    }

    public function testMapDataToFormsHandlesMissingKeys(): void
    {
        $form = new Form('user');
        $form->add('name', 'text');
        $form->add('email', 'email');

        $data = ['name' => 'Partial']; // Missing email

        $this->mapper->mapDataToForms($data, $form);

        $formData = $form->getData();
        $this->assertEquals('Partial', $formData['name']);
        $this->assertArrayNotHasKey('email', $formData);
    }

    public function testMapFormsToDataHandlesEmptyForm(): void
    {
        $form = new Form('user');

        $data = $this->mapper->mapFormsToData($form);

        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function testDeeplyNestedDataMapping(): void
    {
        $form = new Form('root');

        $level1 = new Form('level1');
        $level2 = new Form('level2');
        $level2->add('value', 'text');
        $level1->add('level2', $level2);
        $form->add('level1', $level1);

        $data = [
            'level1' => [
                'level2' => [
                    'value' => 'deep'
                ]
            ]
        ];

        $this->mapper->mapDataToForms($data, $form);

        $result = $this->mapper->mapFormsToData($form);
        $this->assertEquals('deep', $result['level1']['level2']['value']);
    }
}
