<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\RangeSliderManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RangeSliderManager::class)]
class RangeSliderManagerTest extends TestCase
{
    #[Test]
    public function it_has_english_locale_constant(): void
    {
        $locale = RangeSliderManager::LOCALE_EN;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('from', $locale);
        $this->assertArrayHasKey('to', $locale);
        $this->assertSame('From', $locale['from']);
        $this->assertSame('To', $locale['to']);
    }

    #[Test]
    public function it_has_turkish_locale_constant(): void
    {
        $locale = RangeSliderManager::LOCALE_TR;

        $this->assertIsArray($locale);
        $this->assertSame('Başlangıç', $locale['from']);
        $this->assertSame('Bitiş', $locale['to']);
    }

    #[Test]
    public function it_has_german_locale_constant(): void
    {
        $locale = RangeSliderManager::LOCALE_DE;

        $this->assertIsArray($locale);
        $this->assertSame('Von', $locale['from']);
        $this->assertSame('Bis', $locale['to']);
    }

    #[Test]
    public function it_has_french_locale_constant(): void
    {
        $locale = RangeSliderManager::LOCALE_FR;

        $this->assertIsArray($locale);
        $this->assertSame('De', $locale['from']);
        $this->assertSame('À', $locale['to']);
    }

    #[Test]
    public function it_has_spanish_locale_constant(): void
    {
        $locale = RangeSliderManager::LOCALE_ES;

        $this->assertIsArray($locale);
        $this->assertSame('Desde', $locale['from']);
        $this->assertSame('Hasta', $locale['to']);
    }

    #[Test]
    public function it_generates_script_with_default_options(): void
    {
        $script = RangeSliderManager::generateScript('test-input', []);

        $this->assertStringContainsString('<script>', $script);
        $this->assertStringContainsString('</script>', $script);
        $this->assertStringContainsString('RangeSlider_test-input', $script);
        $this->assertStringContainsString('test-input', $script);
    }

    #[Test]
    public function it_generates_script_with_single_handle(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'dual' => false,
            'value' => 50
        ]);

        $this->assertStringContainsString('dual', $script);
        $this->assertStringContainsString('false', $script);
    }

    #[Test]
    public function it_generates_script_with_dual_handles(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'dual' => true,
            'from' => 20,
            'to' => 80
        ]);

        $this->assertStringContainsString('dual', $script);
        $this->assertStringContainsString('true', $script);
        $this->assertStringContainsString('20', $script);
        $this->assertStringContainsString('80', $script);
    }

    #[Test]
    public function it_generates_script_with_min_max_values(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'min' => 0,
            'max' => 1000
        ]);

        $this->assertStringContainsString('min', $script);
        $this->assertStringContainsString('max', $script);
    }

    #[Test]
    public function it_generates_script_with_step(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'step' => 10
        ]);

        $this->assertStringContainsString('step', $script);
        $this->assertStringContainsString('10', $script);
    }

    #[Test]
    public function it_generates_script_with_prefix(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'prefix' => '$'
        ]);

        $this->assertStringContainsString('$', $script);
    }

    #[Test]
    public function it_generates_script_with_suffix(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'suffix' => ' kg'
        ]);

        $this->assertStringContainsString('kg', $script);
    }

    #[Test]
    public function it_generates_script_with_show_value_option(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'showValue' => true
        ]);

        $this->assertStringContainsString('showValue', $script);
    }

    #[Test]
    public function it_generates_script_with_tooltip_option(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'showTooltip' => true
        ]);

        $this->assertStringContainsString('showTooltip', $script);
    }

    #[Test]
    public function it_generates_script_with_vertical_orientation(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'vertical' => true
        ]);

        $this->assertStringContainsString('vertical', $script);
    }

    #[Test]
    public function it_generates_script_with_rtl_option(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('rtl', $script);
        $this->assertStringContainsString('true', $script);
    }

    #[Test]
    public function it_generates_script_with_turkish_locale(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'locale' => RangeSliderManager::LOCALE_TR,
            'dual' => true
        ]);

        $this->assertStringContainsString('Başlangıç', $script);
        $this->assertStringContainsString('Bitiş', $script);
    }

    #[Test]
    public function it_includes_css_styles_in_script(): void
    {
        $script = RangeSliderManager::generateScript('range-input', []);

        $this->assertStringContainsString('<style>', $script);
        $this->assertStringContainsString('</style>', $script);
        $this->assertStringContainsString('.range-slider', $script);
    }

    #[Test]
    public function it_includes_rtl_styles_when_rtl_is_enabled(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('.rtl', $script);
        $this->assertStringContainsString('direction: rtl', $script);
    }

    #[Test]
    public function it_generates_javascript_class(): void
    {
        $script = RangeSliderManager::generateScript('range-input', []);

        $this->assertStringContainsString('class RangeSlider', $script);
        $this->assertStringContainsString('constructor', $script);
        $this->assertStringContainsString('init()', $script);
    }

    #[Test]
    public function it_includes_event_handlers(): void
    {
        $script = RangeSliderManager::generateScript('range-input', []);

        $this->assertStringContainsString('addEventListener', $script);
        $this->assertStringContainsString('mousedown', $script);
    }

    #[Test]
    public function it_generates_unique_namespace_per_input(): void
    {
        $script1 = RangeSliderManager::generateScript('input1', []);
        $script2 = RangeSliderManager::generateScript('input2', []);

        $this->assertStringContainsString('RangeSlider_input1', $script1);
        $this->assertStringContainsString('RangeSlider_input2', $script2);
        $this->assertStringNotContainsString('RangeSlider_input2', $script1);
        $this->assertStringNotContainsString('RangeSlider_input1', $script2);
    }

    #[Test]
    public function it_merges_default_options_with_provided_options(): void
    {
        $script = RangeSliderManager::generateScript('range-input', [
            'min' => 0,
            'max' => 100,
            'prefix' => '$',
            'suffix' => ' USD',
            'locale' => RangeSliderManager::LOCALE_EN
        ]);

        // Should have custom min/max
        $this->assertStringContainsString('min', $script);
        $this->assertStringContainsString('100', $script);

        // Should have prefix and suffix
        $this->assertStringContainsString('$', $script);
        $this->assertStringContainsString('USD', $script);
    }
}
