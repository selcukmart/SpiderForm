<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Translation\Loader;

use FormGenerator\V2\Translation\Loader\PhpLoader;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PhpLoader
 *
 * @covers \FormGenerator\V2\Translation\Loader\PhpLoader
 */
class PhpLoaderTest extends TestCase
{
    private PhpLoader $loader;
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->loader = new PhpLoader();
        $this->fixturesPath = __DIR__ . '/../../../fixtures/translations';
    }

    public function testLoadReturnsArrayFromPhpFile(): void
    {
        $filePath = $this->fixturesPath . '/forms.en_US.php';

        $result = $this->loader->load($filePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('form', $result);
    }

    public function testLoadedArrayHasExpectedStructure(): void
    {
        $filePath = $this->fixturesPath . '/forms.en_US.php';

        $result = $this->loader->load($filePath);

        $this->assertArrayHasKey('form', $result);
        $this->assertArrayHasKey('label', $result['form']);
        $this->assertArrayHasKey('error', $result['form']);
        $this->assertArrayHasKey('button', $result['form']);
    }

    public function testLoadedArrayContainsExpectedTranslations(): void
    {
        $filePath = $this->fixturesPath . '/forms.en_US.php';

        $result = $this->loader->load($filePath);

        $this->assertEquals('Name', $result['form']['label']['name']);
        $this->assertEquals('Email Address', $result['form']['label']['email']);
    }

    public function testLoadThrowsExceptionForNonexistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Translation file not found');

        $this->loader->load('/nonexistent/file.php');
    }

    public function testLoadThrowsExceptionForNonReadableFile(): void
    {
        // Create a temporary non-readable file (if possible)
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '<?php return [];');
        chmod($tempFile, 0000);

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Translation file not readable');

            $this->loader->load($tempFile);
        } finally {
            chmod($tempFile, 0644);
            unlink($tempFile);
        }
    }

    public function testLoadThrowsExceptionWhenFileDoesNotReturnArray(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '<?php return "not an array";');

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Translation file must return an array');

            $this->loader->load($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesEmptyArray(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '<?php return [];');

        try {
            $result = $this->loader->load($tempFile);

            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesNestedArrays(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $content = <<<'PHP'
<?php
return [
    'level1' => [
        'level2' => [
            'level3' => 'deep value'
        ]
    ]
];
PHP;
        file_put_contents($tempFile, $content);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('deep value', $result['level1']['level2']['level3']);
        } finally {
            unlink($tempFile);
        }
    }
}
