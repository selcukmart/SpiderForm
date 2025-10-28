<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Security;

use FormGenerator\V2\Security\{CsrfProtection, CsrfTokenManager, CsrfTokenException};
use FormGenerator\V2\Form\Form;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CsrfProtection
 *
 * @covers \FormGenerator\V2\Security\CsrfProtection
 */
class CsrfProtectionTest extends TestCase
{
    private CsrfProtection $protection;

    protected function setUp(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        $_SESSION = [];

        $this->protection = new CsrfProtection();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testGetTokenManagerReturnsInstance(): void
    {
        $manager = $this->protection->getTokenManager();

        $this->assertInstanceOf(CsrfTokenManager::class, $manager);
    }

    public function testGenerateTokenReturnsString(): void
    {
        $token = $this->protection->generateToken('test_form');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateTokenReturnsTrueForValidToken(): void
    {
        $token = $this->protection->generateToken('test_form');

        $data = ['_csrf_token' => $token];
        $isValid = $this->protection->validateToken('test_form', $data);

        $this->assertTrue($isValid);
    }

    public function testValidateTokenReturnsFalseForInvalidToken(): void
    {
        $this->protection->generateToken('test_form');

        $data = ['_csrf_token' => 'invalid_token'];
        $isValid = $this->protection->validateToken('test_form', $data);

        $this->assertFalse($isValid);
    }

    public function testValidateTokenReturnsFalseWhenTokenMissing(): void
    {
        $this->protection->generateToken('test_form');

        $data = ['other_field' => 'value'];
        $isValid = $this->protection->validateToken('test_form', $data);

        $this->assertFalse($isValid);
    }

    public function testValidateTokenWithCustomFieldName(): void
    {
        $token = $this->protection->generateToken('test_form');

        $data = ['_custom_token' => $token];
        $isValid = $this->protection->validateToken('test_form', $data, '_custom_token');

        $this->assertTrue($isValid);
    }

    public function testGetCsrfFieldHtmlReturnsHiddenInput(): void
    {
        $html = $this->protection->getCsrfFieldHtml('test_form');

        $this->assertStringContainsString('<input type="hidden"', $html);
        $this->assertStringContainsString('name="_csrf_token"', $html);
        $this->assertStringContainsString('value=', $html);
    }

    public function testGetCsrfFieldHtmlEscapesValues(): void
    {
        $html = $this->protection->getCsrfFieldHtml('test_form');

        // Should not contain unescaped special characters
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('">', $html);
    }

    public function testGetCsrfFieldHtmlWithCustomFieldName(): void
    {
        $html = $this->protection->getCsrfFieldHtml('test_form', '_custom_token');

        $this->assertStringContainsString('name="_custom_token"', $html);
    }

    public function testGetCsrfFieldHtmlGeneratesTokenIfNotExists(): void
    {
        $html = $this->protection->getCsrfFieldHtml('new_form');

        $this->assertNotEmpty($html);
        $this->assertTrue($this->protection->getTokenManager()->hasToken('new_form'));
    }

    public function testGetCsrfFieldHtmlReusesExistingToken(): void
    {
        $token = $this->protection->generateToken('test_form');
        $html = $this->protection->getCsrfFieldHtml('test_form');

        $this->assertStringContainsString($token, $html);
    }

    public function testAddCsrfFieldAddsHiddenFieldToForm(): void
    {
        $form = new Form('test_form');
        $this->protection->addCsrfField($form, 'test_form');

        $this->assertTrue($form->has('_csrf_token'));
    }

    public function testAddCsrfFieldWithCustomFieldName(): void
    {
        $form = new Form('test_form');
        $this->protection->addCsrfField($form, 'test_form', '_custom_token');

        $this->assertTrue($form->has('_custom_token'));
        $this->assertFalse($form->has('_csrf_token'));
    }

    public function testValidateFormSubmissionDoesNotThrowForValidToken(): void
    {
        $form = new Form('test_form');
        $token = $this->protection->generateToken('test_form');

        $data = ['_csrf_token' => $token];
        $form->submit($data);

        $this->protection->validateFormSubmission($form, 'test_form', $data);

        $this->assertTrue(true); // No exception thrown
    }

    public function testValidateFormSubmissionThrowsForInvalidToken(): void
    {
        $form = new Form('test_form');
        $this->protection->generateToken('test_form');

        $data = ['_csrf_token' => 'invalid'];
        $form->submit($data);

        $this->expectException(CsrfTokenException::class);
        $this->expectExceptionMessage('Invalid CSRF token');

        $this->protection->validateFormSubmission($form, 'test_form', $data);
    }

    public function testValidateFormSubmissionDoesNothingForUnsubmittedForm(): void
    {
        $form = new Form('test_form');

        $this->protection->validateFormSubmission($form, 'test_form', []);

        $this->assertTrue(true); // No exception thrown
    }

    public function testGetTokenValueReturnsExistingToken(): void
    {
        $generatedToken = $this->protection->generateToken('test_form');
        $retrievedToken = $this->protection->getTokenValue('test_form');

        $this->assertEquals($generatedToken, $retrievedToken);
    }

    public function testGetTokenValueGeneratesNewTokenIfNotExists(): void
    {
        $token = $this->protection->getTokenValue('new_form');

        $this->assertNotEmpty($token);
        $this->assertTrue($this->protection->getTokenManager()->hasToken('new_form'));
    }

    public function testGetCsrfMetaTagsReturnsMetaElements(): void
    {
        $metaTags = $this->protection->getCsrfMetaTags('test_form');

        $this->assertStringContainsString('<meta name="csrf-token"', $metaTags);
        $this->assertStringContainsString('<meta name="csrf-param"', $metaTags);
        $this->assertStringContainsString('content=', $metaTags);
    }

    public function testGetCsrfMetaTagsEscapesTokenValue(): void
    {
        $metaTags = $this->protection->getCsrfMetaTags('test_form');

        // Should not contain unescaped special characters
        $this->assertStringNotContainsString('<script>', $metaTags);
        $this->assertStringNotContainsString('">', $metaTags);
    }

    public function testConstructorAcceptsCustomTokenManager(): void
    {
        $customManager = new CsrfTokenManager();
        $protection = new CsrfProtection($customManager);

        $this->assertSame($customManager, $protection->getTokenManager());
    }

    public function testMultipleFormsCanHaveDifferentTokens(): void
    {
        $token1 = $this->protection->generateToken('form1');
        $token2 = $this->protection->generateToken('form2');

        $this->assertNotEquals($token1, $token2);

        $data1 = ['_csrf_token' => $token1];
        $data2 = ['_csrf_token' => $token2];

        $this->assertTrue($this->protection->validateToken('form1', $data1));
        $this->assertTrue($this->protection->validateToken('form2', $data2));
        $this->assertFalse($this->protection->validateToken('form1', $data2));
        $this->assertFalse($this->protection->validateToken('form2', $data1));
    }

    public function testDefaultFieldNameConstant(): void
    {
        $this->assertEquals('_csrf_token', CsrfProtection::DEFAULT_FIELD_NAME);
    }
}
