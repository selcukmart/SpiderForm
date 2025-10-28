<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Validation;

use FormGenerator\V2\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Test Database Validation Rules (Unique, Exists)
 */
class DatabaseValidationRulesTest extends TestCase
{
    private function createMockPdo(int $count): \PDO
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn($count);

        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        return $pdo;
    }

    public function testUniqueRulePassesWhenValueDoesNotExist(): void
    {
        $pdo = $this->createMockPdo(0); // 0 records found

        $data = ['email' => 'new@example.com'];
        $rules = ['email' => 'unique:users,email'];

        $validator = new Validator($data, $rules);
        $validator->setDatabaseConnection($pdo);

        $this->assertTrue($validator->passes());
    }

    public function testUniqueRuleFailsWhenValueExists(): void
    {
        $pdo = $this->createMockPdo(1); // 1 record found

        $data = ['email' => 'existing@example.com'];
        $rules = ['email' => 'unique:users,email'];

        $validator = new Validator($data, $rules);
        $validator->setDatabaseConnection($pdo);

        $this->assertTrue($validator->fails());
    }

    public function testExistsRulePassesWhenValueExists(): void
    {
        $pdo = $this->createMockPdo(1); // 1 record found

        $data = ['user_id' => 123];
        $rules = ['user_id' => 'exists:users,id'];

        $validator = new Validator($data, $rules);
        $validator->setDatabaseConnection($pdo);

        $this->assertTrue($validator->passes());
    }

    public function testExistsRuleFailsWhenValueDoesNotExist(): void
    {
        $pdo = $this->createMockPdo(0); // 0 records found

        $data = ['user_id' => 999];
        $rules = ['user_id' => 'exists:users,id'];

        $validator = new Validator($data, $rules);
        $validator->setDatabaseConnection($pdo);

        $this->assertTrue($validator->fails());
    }

    public function testUniqueRuleThrowsExceptionWithoutConnection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection not set');

        $data = ['email' => 'test@example.com'];
        $rules = ['email' => 'unique:users,email'];

        $validator = new Validator($data, $rules);
        $validator->passes(); // Should throw exception
    }

    public function testExistsRuleThrowsExceptionWithoutConnection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection not set');

        $data = ['user_id' => 123];
        $rules = ['user_id' => 'exists:users,id'];

        $validator = new Validator($data, $rules);
        $validator->passes(); // Should throw exception
    }

    public function testConfirmedRule(): void
    {
        // Password confirmation does not match
        $data = [
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ];
        $rules = ['password' => 'confirmed'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->fails());

        // Password confirmation matches
        $data = [
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];
        $rules = ['password' => 'confirmed'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testConfirmedRuleWithCustomField(): void
    {
        $data = [
            'password' => 'secret123',
            'password_verify' => 'secret123',
        ];
        $rules = ['password' => 'confirmed:password_verify'];

        $validator = new Validator($data, $rules);
        $this->assertTrue($validator->passes());
    }
}
