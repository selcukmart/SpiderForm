<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Error;

use FormGenerator\V2\Error\ErrorLevel;
use FormGenerator\V2\Error\FormError;
use FormGenerator\V2\Error\ErrorList;
use FormGenerator\V2\Error\ErrorBubblingStrategy;
use FormGenerator\V2\Form\Form;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive unit tests for Error Handling (v2.9.0)
 *
 * @covers \FormGenerator\V2\Error\ErrorLevel
 * @covers \FormGenerator\V2\Error\FormError
 * @covers \FormGenerator\V2\Error\ErrorList
 * @covers \FormGenerator\V2\Error\ErrorBubblingStrategy
 */
class ErrorHandlingTest extends TestCase
{
    // ===== ErrorLevel Tests =====

    public function testErrorLevelValues(): void
    {
        $this->assertEquals('error', ErrorLevel::ERROR->value);
        $this->assertEquals('warning', ErrorLevel::WARNING->value);
        $this->assertEquals('info', ErrorLevel::INFO->value);
    }

    public function testErrorLevelGetLabel(): void
    {
        $this->assertEquals('Error', ErrorLevel::ERROR->getLabel());
        $this->assertEquals('Warning', ErrorLevel::WARNING->getLabel());
        $this->assertEquals('Info', ErrorLevel::INFO->getLabel());
    }

    public function testErrorLevelGetIcon(): void
    {
        $this->assertEquals('❌', ErrorLevel::ERROR->getIcon());
        $this->assertEquals('⚠️', ErrorLevel::WARNING->getIcon());
        $this->assertEquals('ℹ️', ErrorLevel::INFO->getIcon());
    }

    public function testErrorLevelGetCssClass(): void
    {
        $this->assertEquals('error', ErrorLevel::ERROR->getCssClass());
        $this->assertEquals('warning', ErrorLevel::WARNING->getCssClass());
        $this->assertEquals('info', ErrorLevel::INFO->getCssClass());
    }

    public function testErrorLevelIsBlocking(): void
    {
        $this->assertTrue(ErrorLevel::ERROR->isBlocking());
        $this->assertFalse(ErrorLevel::WARNING->isBlocking());
        $this->assertFalse(ErrorLevel::INFO->isBlocking());
    }

    // ===== FormError Tests =====

    public function testFormErrorConstructorStoresMessage(): void
    {
        $error = new FormError('Test error');

        $this->assertEquals('Test error', $error->getMessage());
    }

    public function testFormErrorDefaultLevel(): void
    {
        $error = new FormError('Test error');

        $this->assertEquals(ErrorLevel::ERROR, $error->getLevel());
    }

    public function testFormErrorWithCustomLevel(): void
    {
        $error = new FormError('Warning message', ErrorLevel::WARNING);

        $this->assertEquals(ErrorLevel::WARNING, $error->getLevel());
    }

    public function testFormErrorWithPath(): void
    {
        $error = new FormError('Error', ErrorLevel::ERROR, 'email');

        $this->assertEquals('email', $error->getPath());
    }

    public function testFormErrorWithoutPath(): void
    {
        $error = new FormError('Form-level error');

        $this->assertNull($error->getPath());
    }

    public function testFormErrorParameterInterpolation(): void
    {
        $error = new FormError(
            'Field {{ field }} must be at least {{ min }} characters',
            ErrorLevel::ERROR,
            'password',
            ['field' => 'password', 'min' => 8]
        );

        $this->assertEquals('Field password must be at least 8 characters', $error->getMessage());
    }

    public function testFormErrorGetRawMessage(): void
    {
        $error = new FormError(
            'Value {{ value }} is invalid',
            ErrorLevel::ERROR,
            null,
            ['value' => 'test']
        );

        $this->assertEquals('Value {{ value }} is invalid', $error->getRawMessage());
        $this->assertEquals('Value test is invalid', $error->getMessage());
    }

    public function testFormErrorWithCause(): void
    {
        $cause = new \Exception('Original exception');
        $error = new FormError('Error occurred', ErrorLevel::ERROR, null, [], $cause);

        $this->assertSame($cause, $error->getCause());
    }

    public function testFormErrorWithOrigin(): void
    {
        $form = new Form('test');
        $error = new FormError('Error', ErrorLevel::ERROR, null, [], null, $form);

        $this->assertSame($form, $error->getOrigin());
    }

    public function testFormErrorIsBlocking(): void
    {
        $error = new FormError('Error', ErrorLevel::ERROR);
        $warning = new FormError('Warning', ErrorLevel::WARNING);

        $this->assertTrue($error->isBlocking());
        $this->assertFalse($warning->isBlocking());
    }

    public function testFormErrorToArray(): void
    {
        $error = new FormError(
            'Test {{ value }}',
            ErrorLevel::WARNING,
            'field',
            ['value' => 'test']
        );

        $array = $error->toArray();

        $this->assertEquals('Test test', $array['message']);
        $this->assertEquals('warning', $array['level']);
        $this->assertEquals('field', $array['path']);
        $this->assertEquals(['value' => 'test'], $array['parameters']);
    }

    public function testFormErrorToString(): void
    {
        $error = new FormError('Email is required', ErrorLevel::ERROR, 'email');

        $this->assertEquals('email: Email is required', (string) $error);
    }

    public function testFormErrorToStringWithoutPath(): void
    {
        $error = new FormError('Form error');

        $this->assertEquals('Form error', (string) $error);
    }

    // ===== ErrorList Tests =====

    public function testErrorListConstructor(): void
    {
        $error1 = new FormError('Error 1');
        $error2 = new FormError('Error 2');

        $list = new ErrorList([$error1, $error2]);

        $this->assertCount(2, $list);
    }

    public function testErrorListAdd(): void
    {
        $list = new ErrorList();
        $error = new FormError('Test error');

        $list->add($error);

        $this->assertCount(1, $list);
    }

    public function testErrorListAddReturnsThis(): void
    {
        $list = new ErrorList();
        $error = new FormError('Test');

        $result = $list->add($error);

        $this->assertSame($list, $result);
    }

    public function testErrorListAddAll(): void
    {
        $list = new ErrorList();
        $errors = [
            new FormError('Error 1'),
            new FormError('Error 2'),
            new FormError('Error 3'),
        ];

        $list->addAll($errors);

        $this->assertCount(3, $list);
    }

    public function testErrorListAll(): void
    {
        $error1 = new FormError('Error 1');
        $error2 = new FormError('Error 2');
        $list = new ErrorList([$error1, $error2]);

        $all = $list->all();

        $this->assertIsArray($all);
        $this->assertContains($error1, $all);
        $this->assertContains($error2, $all);
    }

    public function testErrorListByLevel(): void
    {
        $list = new ErrorList([
            new FormError('Error', ErrorLevel::ERROR),
            new FormError('Warning', ErrorLevel::WARNING),
            new FormError('Info', ErrorLevel::INFO),
        ]);

        $errors = $list->byLevel(ErrorLevel::ERROR);
        $warnings = $list->byLevel(ErrorLevel::WARNING);

        $this->assertCount(1, $errors);
        $this->assertCount(1, $warnings);
    }

    public function testErrorListByPath(): void
    {
        $list = new ErrorList([
            new FormError('Error 1', ErrorLevel::ERROR, 'email'),
            new FormError('Error 2', ErrorLevel::ERROR, 'password'),
            new FormError('Error 3', ErrorLevel::ERROR, 'email'),
        ]);

        $emailErrors = $list->byPath('email');

        $this->assertCount(2, $emailErrors);
    }

    public function testErrorListByPathDeep(): void
    {
        $list = new ErrorList([
            new FormError('Error 1', ErrorLevel::ERROR, 'address'),
            new FormError('Error 2', ErrorLevel::ERROR, 'address.street'),
            new FormError('Error 3', ErrorLevel::ERROR, 'address.city'),
            new FormError('Error 4', ErrorLevel::ERROR, 'email'),
        ]);

        $addressErrors = $list->byPath('address', deep: true);

        $this->assertCount(3, $addressErrors);
    }

    public function testErrorListBlocking(): void
    {
        $list = new ErrorList([
            new FormError('Error', ErrorLevel::ERROR),
            new FormError('Warning', ErrorLevel::WARNING),
            new FormError('Info', ErrorLevel::INFO),
        ]);

        $blocking = $list->blocking();

        $this->assertCount(1, $blocking);
    }

    public function testErrorListHasBlocking(): void
    {
        $list = new ErrorList([
            new FormError('Warning', ErrorLevel::WARNING),
        ]);

        $this->assertFalse($list->hasBlocking());

        $list->add(new FormError('Error', ErrorLevel::ERROR));

        $this->assertTrue($list->hasBlocking());
    }

    public function testErrorListToArray(): void
    {
        $list = new ErrorList([
            new FormError('Email required', ErrorLevel::ERROR, 'email'),
            new FormError('Invalid street', ErrorLevel::ERROR, 'address.street'),
        ]);

        $array = $list->toArray();

        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('address', $array);
        $this->assertContains('Email required', $array['email']);
        $this->assertContains('Invalid street', $array['address']['street']);
    }

    public function testErrorListToFlat(): void
    {
        $list = new ErrorList([
            new FormError('Email required', ErrorLevel::ERROR, 'email'),
            new FormError('Invalid street', ErrorLevel::ERROR, 'address.street'),
        ]);

        $flat = $list->toFlat();

        $this->assertEquals('Email required', $flat['email']);
        $this->assertEquals('Invalid street', $flat['address.street']);
    }

    public function testErrorListFirst(): void
    {
        $error1 = new FormError('First', ErrorLevel::ERROR, 'field1');
        $error2 = new FormError('Second', ErrorLevel::ERROR, 'field2');
        $list = new ErrorList([$error1, $error2]);

        $this->assertSame($error1, $list->first());
        $this->assertSame($error1, $list->first('field1'));
        $this->assertSame($error2, $list->first('field2'));
        $this->assertNull($list->first('nonexistent'));
    }

    public function testErrorListIsEmpty(): void
    {
        $list = new ErrorList();

        $this->assertTrue($list->isEmpty());

        $list->add(new FormError('Error'));

        $this->assertFalse($list->isEmpty());
    }

    public function testErrorListCount(): void
    {
        $list = new ErrorList();

        $this->assertEquals(0, $list->count());

        $list->add(new FormError('Error 1'));
        $list->add(new FormError('Error 2'));

        $this->assertEquals(2, $list->count());
    }

    public function testErrorListIteration(): void
    {
        $error1 = new FormError('Error 1');
        $error2 = new FormError('Error 2');
        $list = new ErrorList([$error1, $error2]);

        $iterated = [];
        foreach ($list as $error) {
            $iterated[] = $error;
        }

        $this->assertCount(2, $iterated);
        $this->assertContains($error1, $iterated);
        $this->assertContains($error2, $iterated);
    }

    public function testErrorListClear(): void
    {
        $list = new ErrorList([new FormError('Error')]);

        $list->clear();

        $this->assertTrue($list->isEmpty());
    }

    public function testErrorListMerge(): void
    {
        $list1 = new ErrorList([new FormError('Error 1')]);
        $list2 = new ErrorList([new FormError('Error 2')]);

        $merged = $list1->merge($list2);

        $this->assertCount(2, $merged);
        $this->assertCount(1, $list1); // Original unchanged
    }

    public function testErrorListToString(): void
    {
        $list = new ErrorList([
            new FormError('Error 1', ErrorLevel::ERROR, 'field1'),
            new FormError('Error 2', ErrorLevel::ERROR, 'field2'),
        ]);

        $string = (string) $list;

        $this->assertStringContainsString('field1: Error 1', $string);
        $this->assertStringContainsString('field2: Error 2', $string);
    }

    // ===== ErrorBubblingStrategy Tests =====

    public function testErrorBubblingStrategyDefaultEnabled(): void
    {
        $strategy = new ErrorBubblingStrategy();

        $this->assertTrue($strategy->isEnabled());
    }

    public function testErrorBubblingStrategyDisabled(): void
    {
        $strategy = ErrorBubblingStrategy::disabled();

        $this->assertFalse($strategy->isEnabled());
    }

    public function testErrorBubblingStrategyEnabled(): void
    {
        $strategy = ErrorBubblingStrategy::enabled();

        $this->assertTrue($strategy->isEnabled());
    }

    public function testErrorBubblingStrategyCollectErrorsWhenDisabled(): void
    {
        $strategy = ErrorBubblingStrategy::disabled();
        $parent = new Form('parent');
        $child = new Form('child');

        $errors = $strategy->collectErrors($parent, $child);

        $this->assertTrue($errors->isEmpty());
    }

    public function testErrorBubblingStrategyShouldBubbleWhenDisabled(): void
    {
        $strategy = ErrorBubblingStrategy::disabled();
        $error = new FormError('Test');

        $this->assertFalse($strategy->shouldBubble($error));
    }

    public function testErrorBubblingStrategyShouldBubbleWithDepthLimit(): void
    {
        $strategy = ErrorBubblingStrategy::withDepthLimit(2);
        $error = new FormError('Test');

        $this->assertTrue($strategy->shouldBubble($error, 0));
        $this->assertTrue($strategy->shouldBubble($error, 1));
        $this->assertFalse($strategy->shouldBubble($error, 2));
    }

    public function testErrorBubblingStrategyStopOnBlocking(): void
    {
        $strategy = ErrorBubblingStrategy::stopOnBlocking();
        $error = new FormError('Error', ErrorLevel::ERROR);
        $warning = new FormError('Warning', ErrorLevel::WARNING);

        $this->assertFalse($strategy->shouldBubble($error));
        $this->assertTrue($strategy->shouldBubble($warning));
    }

    public function testComplexErrorScenario(): void
    {
        // Create an error list with mixed severities and paths
        $list = new ErrorList([
            new FormError('Email is required', ErrorLevel::ERROR, 'email'),
            new FormError('Weak password', ErrorLevel::WARNING, 'password'),
            new FormError('Consider adding a profile picture', ErrorLevel::INFO, 'avatar'),
            new FormError('Invalid street', ErrorLevel::ERROR, 'address.street'),
            new FormError('ZIP code format incorrect', ErrorLevel::WARNING, 'address.zipcode'),
        ]);

        // Test filtering by level
        $criticalErrors = $list->byLevel(ErrorLevel::ERROR);
        $this->assertCount(2, $criticalErrors);

        // Test path filtering
        $addressErrors = $list->byPath('address', deep: true);
        $this->assertCount(2, $addressErrors);

        // Test blocking
        $this->assertTrue($list->hasBlocking());

        // Test conversion
        $flat = $list->toFlat();
        $this->assertArrayHasKey('email', $flat);
        $this->assertArrayHasKey('address.street', $flat);
    }
}
