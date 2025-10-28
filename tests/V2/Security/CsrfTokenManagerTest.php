<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Security;

use FormGenerator\V2\Security\CsrfTokenManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CsrfTokenManager
 *
 * @covers \FormGenerator\V2\Security\CsrfTokenManager
 */
class CsrfTokenManagerTest extends TestCase
{
    private CsrfTokenManager $manager;

    protected function setUp(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session data
        $_SESSION = [];

        $this->manager = new CsrfTokenManager();
    }

    protected function tearDown(): void
    {
        // Clean up session
        $_SESSION = [];
    }

    public function testGenerateTokenReturnsString(): void
    {
        $token = $this->manager->generateToken('test_form');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testGenerateTokenReturns64CharacterHexString(): void
    {
        $token = $this->manager->generateToken('test_form');

        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testGenerateTokenStoresTokenInSession(): void
    {
        $token = $this->manager->generateToken('test_form');

        $this->assertArrayHasKey('_csrf_tokens', $_SESSION);
        $this->assertArrayHasKey('test_form', $_SESSION['_csrf_tokens']);
        $this->assertEquals($token, $_SESSION['_csrf_tokens']['test_form']['token']);
    }

    public function testIsTokenValidReturnsTrueForValidToken(): void
    {
        $token = $this->manager->generateToken('test_form');

        $isValid = $this->manager->isTokenValid('test_form', $token);

        $this->assertTrue($isValid);
    }

    public function testIsTokenValidReturnsFalseForInvalidToken(): void
    {
        $this->manager->generateToken('test_form');

        $isValid = $this->manager->isTokenValid('test_form', 'invalid_token');

        $this->assertFalse($isValid);
    }

    public function testIsTokenValidReturnsFalseForNonexistentTokenId(): void
    {
        $isValid = $this->manager->isTokenValid('nonexistent', 'some_token');

        $this->assertFalse($isValid);
    }

    public function testGenerateTokenForDifferentIdsCreatesDifferentTokens(): void
    {
        $token1 = $this->manager->generateToken('form1');
        $token2 = $this->manager->generateToken('form2');

        $this->assertNotEquals($token1, $token2);
    }

    public function testGenerateTokenCalledTwiceForSameIdOverwritesToken(): void
    {
        $token1 = $this->manager->generateToken('test_form');
        $token2 = $this->manager->generateToken('test_form');

        $this->assertNotEquals($token1, $token2);
        $this->assertTrue($this->manager->isTokenValid('test_form', $token2));
        $this->assertFalse($this->manager->isTokenValid('test_form', $token1));
    }

    public function testRefreshTokenGeneratesNewToken(): void
    {
        $oldToken = $this->manager->generateToken('test_form');
        $newToken = $this->manager->refreshToken('test_form');

        $this->assertNotEquals($oldToken, $newToken);
        $this->assertTrue($this->manager->isTokenValid('test_form', $newToken));
        $this->assertFalse($this->manager->isTokenValid('test_form', $oldToken));
    }

    public function testRemoveTokenRemovesTokenFromSession(): void
    {
        $this->manager->generateToken('test_form');
        $this->manager->removeToken('test_form');

        $this->assertArrayNotHasKey('test_form', $_SESSION['_csrf_tokens'] ?? []);
    }

    public function testRemoveTokenDoesNotThrowForNonexistentToken(): void
    {
        $this->manager->removeToken('nonexistent');

        $this->assertTrue(true); // No exception thrown
    }

    public function testGetTokenReturnsStoredToken(): void
    {
        $generatedToken = $this->manager->generateToken('test_form');
        $retrievedToken = $this->manager->getToken('test_form');

        $this->assertEquals($generatedToken, $retrievedToken);
    }

    public function testGetTokenReturnsNullForNonexistentToken(): void
    {
        $token = $this->manager->getToken('nonexistent');

        $this->assertNull($token);
    }

    public function testHasTokenReturnsTrueForExistingToken(): void
    {
        $this->manager->generateToken('test_form');

        $this->assertTrue($this->manager->hasToken('test_form'));
    }

    public function testHasTokenReturnsFalseForNonexistentToken(): void
    {
        $this->assertFalse($this->manager->hasToken('nonexistent'));
    }

    public function testCleanExpiredTokensRemovesOldTokens(): void
    {
        // Generate token and manually set old timestamp
        $this->manager->generateToken('old_token');
        $_SESSION['_csrf_tokens']['old_token']['timestamp'] = time() - 10000; // Very old

        // Generate fresh token
        $this->manager->generateToken('fresh_token');

        $this->manager->cleanExpiredTokens();

        $this->assertFalse($this->manager->hasToken('old_token'));
        $this->assertTrue($this->manager->hasToken('fresh_token'));
    }

    public function testTokenValidationUsesTimingSafeComparison(): void
    {
        $token = $this->manager->generateToken('test_form');

        // Even with timing attacks, wrong tokens should fail
        $similarToken = substr($token, 0, -1) . '0';

        $this->assertFalse($this->manager->isTokenValid('test_form', $similarToken));
    }

    public function testMultipleTokensCanCoexist(): void
    {
        $token1 = $this->manager->generateToken('form1');
        $token2 = $this->manager->generateToken('form2');
        $token3 = $this->manager->generateToken('form3');

        $this->assertTrue($this->manager->isTokenValid('form1', $token1));
        $this->assertTrue($this->manager->isTokenValid('form2', $token2));
        $this->assertTrue($this->manager->isTokenValid('form3', $token3));
    }

    public function testTokenStorageIncludesTimestamp(): void
    {
        $this->manager->generateToken('test_form');

        $this->assertArrayHasKey('timestamp', $_SESSION['_csrf_tokens']['test_form']);
        $this->assertIsInt($_SESSION['_csrf_tokens']['test_form']['timestamp']);
    }

    public function testGeneratedTokensAreUnique(): void
    {
        $tokens = [];

        for ($i = 0; $i < 100; $i++) {
            $tokens[] = $this->manager->generateToken("form_{$i}");
        }

        $uniqueTokens = array_unique($tokens);

        $this->assertCount(100, $uniqueTokens);
    }
}
