<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\DependencyManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DependencyManager::class)]
class DependencyManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset static state
        DependencyManager::reset();
    }

    #[Test]
    public function it_generates_javascript_code(): void
    {
        $js = DependencyManager::generateScript('test-form');
        
        $this->assertIsJavaScript($js);
        $this->assertStringContainsString('<script>', $js);
        $this->assertStringContainsString('FormGen_test_form', $js);
    }

    #[Test]
    public function it_prevents_duplicate_generation(): void
    {
        $js1 = DependencyManager::generateScript('test-form');
        $js2 = DependencyManager::generateScript('test-form');
        
        $this->assertNotEmpty($js1);
        $this->assertEmpty($js2);
    }

    #[Test]
    public function it_generates_unique_namespace_per_form(): void
    {
        DependencyManager::reset();
        
        $js1 = DependencyManager::generateScript('form1');
        $js2 = DependencyManager::generateScript('form2');
        
        $this->assertStringContainsString('FormGen_form1', $js1);
        $this->assertStringContainsString('FormGen_form2', $js2);
        $this->assertStringNotContainsString('FormGen_form2', $js1);
    }

    #[Test]
    public function it_includes_initialization_function(): void
    {
        $js = DependencyManager::generateScript('test-form');
        
        $this->assertJavaScriptContainsFunction($js, 'init');
    }

    #[Test]
    public function it_includes_toggle_dependents_function(): void
    {
        $js = DependencyManager::generateScript('test-form');
        
        $this->assertStringContainsString('toggleDependents', $js);
    }

    #[Test]
    public function it_includes_show_hide_functions(): void
    {
        $js = DependencyManager::generateScript('test-form');
        
        $this->assertStringContainsString('showElement', $js);
        $this->assertStringContainsString('hideElement', $js);
    }

    #[Test]
    public function it_auto_initializes_on_dom_ready(): void
    {
        $js = DependencyManager::generateScript('test-form');
        
        $this->assertStringContainsString('DOMContentLoaded', $js);
    }

    #[Test]
    public function it_can_generate_without_script_tags(): void
    {
        $js = DependencyManager::generateScript('test-form', false);
        
        $this->assertStringNotContainsString('<script>', $js);
        $this->assertIsJavaScript($js);
    }

    #[Test]
    public function it_sanitizes_form_id_for_namespace(): void
    {
        $js = DependencyManager::generateScript('my-form-123');
        
        // Should convert hyphens to underscores for valid JavaScript identifier
        $this->assertStringContainsString('FormGen_my_form_123', $js);
    }
}
