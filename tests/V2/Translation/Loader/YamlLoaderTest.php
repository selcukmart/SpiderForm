<?php

declare(strict_types=1);

namespace FormGenerator\Tests\V2\Translation\Loader;

use FormGenerator\V2\Translation\Loader\YamlLoader;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for YamlLoader
 *
 * @covers \FormGenerator\V2\Translation\Loader\YamlLoader
 */
class YamlLoaderTest extends TestCase
{
    private YamlLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new YamlLoader();
    }

    public function testLoadReturnsArrayFromYamlFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
form:
  label:
    name: "Name"
    email: "Email"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('form', $result);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadParsesSimpleYaml(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
form:
  label:
    name: "Name"
    email: "Email Address"
  button:
    submit: "Submit"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('Name', $result['form']['label']['name']);
            $this->assertEquals('Email Address', $result['form']['label']['email']);
            $this->assertEquals('Submit', $result['form']['button']['submit']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesCommentsAndEmptyLines(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
# This is a comment
form:
  label:
    # Another comment
    name: "Name"

    email: "Email"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('Name', $result['form']['label']['name']);
            $this->assertEquals('Email', $result['form']['label']['email']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesQuotedValues(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
test:
  single: 'Single quoted'
  double: "Double quoted"
  none: Unquoted
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('Single quoted', $result['test']['single']);
            $this->assertEquals('Double quoted', $result['test']['double']);
            $this->assertEquals('Unquoted', $result['test']['none']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadThrowsExceptionForNonexistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Translation file not found');

        $this->loader->load('/nonexistent/file.yaml');
    }

    public function testLoadHandlesNestedStructures(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
level1:
  level2:
    level3:
      value: "deep"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('deep', $result['level1']['level2']['level3']['value']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesEmptyParentNodes(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
parent:
  child1: "value1"
  child2: "value2"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertIsArray($result['parent']);
            $this->assertEquals('value1', $result['parent']['child1']);
            $this->assertEquals('value2', $result['parent']['child2']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesMultipleRootKeys(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
form:
  label:
    name: "Name"
validation:
  error:
    required: "Required"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertArrayHasKey('form', $result);
            $this->assertArrayHasKey('validation', $result);
            $this->assertEquals('Name', $result['form']['label']['name']);
            $this->assertEquals('Required', $result['validation']['error']['required']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testLoadHandlesColonInValue(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.yaml';
        $yaml = <<<'YAML'
test:
  url: "https://example.com"
  time: "12:30:45"
YAML;
        file_put_contents($tempFile, $yaml);

        try {
            $result = $this->loader->load($tempFile);

            $this->assertEquals('https://example.com', $result['test']['url']);
            $this->assertEquals('12:30:45', $result['test']['time']);
        } finally {
            unlink($tempFile);
        }
    }
}
