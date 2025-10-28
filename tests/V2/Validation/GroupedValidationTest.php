<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Validation;

use FormGenerator\V2\Validation\GroupedValidation;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for GroupedValidation
 *
 * @covers \FormGenerator\V2\Validation\GroupedValidation
 */
class GroupedValidationTest extends TestCase
{
    private GroupedValidation $validation;

    protected function setUp(): void
    {
        $this->validation = new GroupedValidation();
    }

    public function testAddRuleStoresRule(): void
    {
        $this->validation->addRule('email', 'required');

        $rules = $this->validation->getRulesForField('email');

        $this->assertContains('required', $rules);
    }

    public function testAddRuleDefaultsToDefaultGroup(): void
    {
        $this->validation->addRule('email', 'required');

        $rules = $this->validation->getRulesForField('email', ['Default']);

        $this->assertContains('required', $rules);
    }

    public function testAddRuleWithCustomGroup(): void
    {
        $this->validation->addRule('username', 'required', ['groups' => ['registration']]);

        $rules = $this->validation->getRulesForField('username', ['registration']);

        $this->assertContains('required', $rules);
    }

    public function testAddRuleWithMultipleGroups(): void
    {
        $this->validation->addRule('email', 'required', ['groups' => ['registration', 'profile']]);

        $registrationRules = $this->validation->getRulesForField('email', ['registration']);
        $profileRules = $this->validation->getRulesForField('email', ['profile']);

        $this->assertContains('required', $registrationRules);
        $this->assertContains('required', $profileRules);
    }

    public function testGetRulesForFieldReturnsEmptyForNonexistentField(): void
    {
        $rules = $this->validation->getRulesForField('nonexistent');

        $this->assertIsArray($rules);
        $this->assertEmpty($rules);
    }

    public function testGetRulesForFieldFiltersBy Group(): void
    {
        $this->validation->addRule('username', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('username', 'minLength:3', ['groups' => ['profile']]);

        $registrationRules = $this->validation->getRulesForField('username', ['registration']);
        $profileRules = $this->validation->getRulesForField('username', ['profile']);

        $this->assertContains('required', $registrationRules);
        $this->assertNotContains('minLength:3', $registrationRules);

        $this->assertContains('minLength:3', $profileRules);
        $this->assertNotContains('required', $profileRules);
    }

    public function testGetRulesForFieldReturnsAllMatchingGroupRules(): void
    {
        $this->validation->addRule('email', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('email', 'email', ['groups' => ['registration']]);
        $this->validation->addRule('email', 'unique', ['groups' => ['profile']]);

        $rules = $this->validation->getRulesForField('email', ['registration']);

        $this->assertCount(2, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
        $this->assertNotContains('unique', $rules);
    }

    public function testGetRulesForFieldSupportsMultipleValidateGroups(): void
    {
        $this->validation->addRule('field', 'rule1', ['groups' => ['group1']]);
        $this->validation->addRule('field', 'rule2', ['groups' => ['group2']]);
        $this->validation->addRule('field', 'rule3', ['groups' => ['group3']]);

        $rules = $this->validation->getRulesForField('field', ['group1', 'group2']);

        $this->assertContains('rule1', $rules);
        $this->assertContains('rule2', $rules);
        $this->assertNotContains('rule3', $rules);
    }

    public function testGetAllRulesReturnsAllFieldRules(): void
    {
        $this->validation->addRule('email', 'required');
        $this->validation->addRule('email', 'email');
        $this->validation->addRule('password', 'required');

        $allRules = $this->validation->getAllRules();

        $this->assertArrayHasKey('email', $allRules);
        $this->assertArrayHasKey('password', $allRules);
        $this->assertEquals('required|email', $allRules['email']);
        $this->assertEquals('required', $allRules['password']);
    }

    public function testGetAllRulesFiltersBy Groups(): void
    {
        $this->validation->addRule('username', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('email', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('bio', 'minLength:10', ['groups' => ['profile']]);

        $registrationRules = $this->validation->getAllRules(['registration']);
        $profileRules = $this->validation->getAllRules(['profile']);

        $this->assertArrayHasKey('username', $registrationRules);
        $this->assertArrayHasKey('email', $registrationRules);
        $this->assertArrayNotHasKey('bio', $registrationRules);

        $this->assertArrayHasKey('bio', $profileRules);
        $this->assertArrayNotHasKey('username', $profileRules);
    }

    public function testGetGroupsReturnsAllRegisteredGroups(): void
    {
        $this->validation->addRule('field1', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('field2', 'required', ['groups' => ['profile']]);
        $this->validation->addRule('field3', 'required', ['groups' => ['admin']]);

        $groups = $this->validation->getGroups();

        $this->assertContains('registration', $groups);
        $this->assertContains('profile', $groups);
        $this->assertContains('admin', $groups);
        $this->assertCount(3, $groups);
    }

    public function testGetGroupsIncludesDefault(): void
    {
        $this->validation->addRule('email', 'required'); // Uses Default group

        $groups = $this->validation->getGroups();

        $this->assertContains('Default', $groups);
    }

    public function testHasGroupReturnsTrueForExistingGroup(): void
    {
        $this->validation->addRule('field', 'required', ['groups' => ['registration']]);

        $this->assertTrue($this->validation->hasGroup('registration'));
        $this->assertFalse($this->validation->hasGroup('nonexistent'));
    }

    public function testClearRemovesAllRulesAndGroups(): void
    {
        $this->validation->addRule('email', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('password', 'required', ['groups' => ['profile']]);

        $this->validation->clear();

        $this->assertEmpty($this->validation->getAllRules());
        $this->assertEmpty($this->validation->getGroups());
    }

    public function testDefaultGroupConstant(): void
    {
        $this->assertEquals('Default', GroupedValidation::DEFAULT_GROUP);
    }

    public function testMultipleRulesForSameFieldInSameGroup(): void
    {
        $this->validation->addRule('password', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('password', 'minLength:8', ['groups' => ['registration']]);
        $this->validation->addRule('password', 'hasUppercase', ['groups' => ['registration']]);

        $rules = $this->validation->getRulesForField('password', ['registration']);

        $this->assertCount(3, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('minLength:8', $rules);
        $this->assertContains('hasUppercase', $rules);
    }

    public function testComplexGroupScenario(): void
    {
        // Registration: username, email, password required
        $this->validation->addRule('username', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('username', 'minLength:3', ['groups' => ['registration']]);
        $this->validation->addRule('email', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('email', 'email', ['groups' => ['registration']]);
        $this->validation->addRule('password', 'required', ['groups' => ['registration']]);
        $this->validation->addRule('password', 'minLength:8', ['groups' => ['registration']]);

        // Profile: bio, avatar optional
        $this->validation->addRule('bio', 'maxLength:500', ['groups' => ['profile']]);
        $this->validation->addRule('avatar', 'image', ['groups' => ['profile']]);

        // Admin: role required
        $this->validation->addRule('role', 'required', ['groups' => ['admin']]);
        $this->validation->addRule('role', 'in:admin,moderator,user', ['groups' => ['admin']]);

        $registrationRules = $this->validation->getAllRules(['registration']);
        $profileRules = $this->validation->getAllRules(['profile']);
        $adminRules = $this->validation->getAllRules(['admin']);

        // Registration should have 3 fields
        $this->assertCount(3, $registrationRules);
        $this->assertArrayHasKey('username', $registrationRules);
        $this->assertArrayHasKey('email', $registrationRules);
        $this->assertArrayHasKey('password', $registrationRules);

        // Profile should have 2 fields
        $this->assertCount(2, $profileRules);
        $this->assertArrayHasKey('bio', $profileRules);
        $this->assertArrayHasKey('avatar', $profileRules);

        // Admin should have 1 field
        $this->assertCount(1, $adminRules);
        $this->assertArrayHasKey('role', $adminRules);
    }

    public function testGetAllRulesJoinsRulesWithPipe(): void
    {
        $this->validation->addRule('email', 'required');
        $this->validation->addRule('email', 'email');
        $this->validation->addRule('email', 'unique');

        $allRules = $this->validation->getAllRules();

        $this->assertEquals('required|email|unique', $allRules['email']);
    }

    public function testRuleOptionsAreStoredButNotUsedInGetRules(): void
    {
        $this->validation->addRule('password', 'minLength', [
            'value' => 8,
            'groups' => ['registration']
        ]);

        $rules = $this->validation->getRulesForField('password', ['registration']);

        // Should only return the rule name, not the full options
        $this->assertContains('minLength', $rules);
    }

    public function testEmptyGroupsArrayUsesDefault(): void
    {
        $this->validation->addRule('email', 'required');

        $rules = $this->validation->getAllRules([]);

        // Should return nothing since we're not validating Default group
        $this->assertEmpty($rules);
    }

    public function testMultipleFieldsInMultipleGroups(): void
    {
        // Field in both registration and profile
        $this->validation->addRule('email', 'required', ['groups' => ['registration', 'profile']]);
        $this->validation->addRule('email', 'email', ['groups' => ['registration', 'profile']]);

        // Field only in registration
        $this->validation->addRule('password', 'required', ['groups' => ['registration']]);

        // Field only in profile
        $this->validation->addRule('bio', 'maxLength:500', ['groups' => ['profile']]);

        $registrationRules = $this->validation->getAllRules(['registration']);
        $profileRules = $this->validation->getAllRules(['profile']);

        // Registration: email + password
        $this->assertCount(2, $registrationRules);
        $this->assertArrayHasKey('email', $registrationRules);
        $this->assertArrayHasKey('password', $registrationRules);

        // Profile: email + bio
        $this->assertCount(2, $profileRules);
        $this->assertArrayHasKey('email', $profileRules);
        $this->assertArrayHasKey('bio', $profileRules);
    }
}
