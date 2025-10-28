<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Builder;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Builder\TimePickerManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TimePickerManager::class)]
class TimePickerManagerTest extends TestCase
{
    #[Test]
    public function it_has_english_locale_constant(): void
    {
        $locale = TimePickerManager::LOCALE_EN;

        $this->assertIsArray($locale);
        $this->assertArrayHasKey('hours', $locale);
        $this->assertArrayHasKey('minutes', $locale);
        $this->assertArrayHasKey('seconds', $locale);
        $this->assertSame('Hours', $locale['hours']);
        $this->assertSame('Minutes', $locale['minutes']);
    }

    #[Test]
    public function it_has_turkish_locale_constant(): void
    {
        $locale = TimePickerManager::LOCALE_TR;

        $this->assertIsArray($locale);
        $this->assertSame('Saat', $locale['hours']);
        $this->assertSame('Dakika', $locale['minutes']);
        $this->assertSame('Saniye', $locale['seconds']);
    }

    #[Test]
    public function it_has_german_locale_constant(): void
    {
        $locale = TimePickerManager::LOCALE_DE;

        $this->assertIsArray($locale);
        $this->assertSame('Stunden', $locale['hours']);
        $this->assertSame('Minuten', $locale['minutes']);
    }

    #[Test]
    public function it_has_french_locale_constant(): void
    {
        $locale = TimePickerManager::LOCALE_FR;

        $this->assertIsArray($locale);
        $this->assertSame('Heures', $locale['hours']);
        $this->assertSame('Minutes', $locale['minutes']);
    }

    #[Test]
    public function it_has_spanish_locale_constant(): void
    {
        $locale = TimePickerManager::LOCALE_ES;

        $this->assertIsArray($locale);
        $this->assertSame('Horas', $locale['hours']);
        $this->assertSame('Minutos', $locale['minutes']);
    }

    #[Test]
    public function it_generates_script_with_default_options(): void
    {
        $script = TimePickerManager::generateScript('test-input', []);

        $this->assertStringContainsString('<script>', $script);
        $this->assertStringContainsString('</script>', $script);
        $this->assertStringContainsString('TimePicker_test-input', $script);
        $this->assertStringContainsString('test-input', $script);
    }

    #[Test]
    public function it_generates_script_with_12_hour_format(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'format' => '12'
        ]);

        $this->assertStringContainsString('AM', $script);
        $this->assertStringContainsString('PM', $script);
    }

    #[Test]
    public function it_generates_script_with_24_hour_format(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'format' => '24'
        ]);

        $this->assertStringContainsString('24', $script);
    }

    #[Test]
    public function it_generates_script_with_seconds(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'showSeconds' => true
        ]);

        $this->assertStringContainsString('showSeconds', $script);
    }

    #[Test]
    public function it_generates_script_without_seconds(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'showSeconds' => false
        ]);

        $this->assertStringContainsString('false', $script);
    }

    #[Test]
    public function it_generates_script_with_step_interval(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'step' => 15
        ]);

        $this->assertStringContainsString('step', $script);
        $this->assertStringContainsString('15', $script);
    }

    #[Test]
    public function it_generates_script_with_min_max_time(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'minTime' => '09:00',
            'maxTime' => '17:00'
        ]);

        $this->assertStringContainsString('09:00', $script);
        $this->assertStringContainsString('17:00', $script);
    }

    #[Test]
    public function it_generates_script_with_rtl_option(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('rtl', $script);
        $this->assertStringContainsString('true', $script);
    }

    #[Test]
    public function it_generates_script_with_turkish_locale(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'locale' => TimePickerManager::LOCALE_TR
        ]);

        $this->assertStringContainsString('Saat', $script);
        $this->assertStringContainsString('Dakika', $script);
    }

    #[Test]
    public function it_includes_css_styles_in_script(): void
    {
        $script = TimePickerManager::generateScript('time-input', []);

        $this->assertStringContainsString('<style>', $script);
        $this->assertStringContainsString('</style>', $script);
        $this->assertStringContainsString('.timepicker-dropdown', $script);
    }

    #[Test]
    public function it_includes_rtl_styles_when_rtl_is_enabled(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'rtl' => true
        ]);

        $this->assertStringContainsString('.rtl', $script);
        $this->assertStringContainsString('direction: rtl', $script);
    }

    #[Test]
    public function it_generates_javascript_class(): void
    {
        $script = TimePickerManager::generateScript('time-input', []);

        $this->assertStringContainsString('class TimePicker', $script);
        $this->assertStringContainsString('constructor', $script);
        $this->assertStringContainsString('init()', $script);
    }

    #[Test]
    public function it_includes_event_handlers(): void
    {
        $script = TimePickerManager::generateScript('time-input', []);

        $this->assertStringContainsString('addEventListener', $script);
        $this->assertStringContainsString('click', $script);
    }

    #[Test]
    public function it_generates_unique_namespace_per_input(): void
    {
        $script1 = TimePickerManager::generateScript('input1', []);
        $script2 = TimePickerManager::generateScript('input2', []);

        $this->assertStringContainsString('TimePicker_input1', $script1);
        $this->assertStringContainsString('TimePicker_input2', $script2);
        $this->assertStringNotContainsString('TimePicker_input2', $script1);
        $this->assertStringNotContainsString('TimePicker_input1', $script2);
    }

    #[Test]
    public function it_generates_script_with_inline_mode(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'inline' => true
        ]);

        $this->assertStringContainsString('inline', $script);
    }

    #[Test]
    public function it_merges_default_options_with_provided_options(): void
    {
        $script = TimePickerManager::generateScript('time-input', [
            'format' => '12',
            'locale' => TimePickerManager::LOCALE_TR,
            'showSeconds' => true
        ]);

        // Should have 12-hour format
        $this->assertStringContainsString('AM', $script);

        // Should have Turkish locale
        $this->assertStringContainsString('Saat', $script);

        // Should show seconds
        $this->assertStringContainsString('showSeconds', $script);
    }
}
