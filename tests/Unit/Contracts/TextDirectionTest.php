<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Contracts;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Contracts\TextDirection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TextDirection::class)]
class TextDirectionTest extends TestCase
{
    #[Test]
    public function it_has_ltr_case(): void
    {
        $direction = TextDirection::LTR;

        $this->assertInstanceOf(TextDirection::class, $direction);
        $this->assertSame('ltr', $direction->value);
    }

    #[Test]
    public function it_has_rtl_case(): void
    {
        $direction = TextDirection::RTL;

        $this->assertInstanceOf(TextDirection::class, $direction);
        $this->assertSame('rtl', $direction->value);
    }

    #[Test]
    public function it_checks_if_rtl(): void
    {
        $this->assertTrue(TextDirection::RTL->isRtl());
        $this->assertFalse(TextDirection::LTR->isRtl());
    }

    #[Test]
    public function it_checks_if_ltr(): void
    {
        $this->assertTrue(TextDirection::LTR->isLtr());
        $this->assertFalse(TextDirection::RTL->isLtr());
    }

    #[Test]
    public function it_returns_opposite_direction(): void
    {
        $this->assertSame(TextDirection::RTL, TextDirection::LTR->opposite());
        $this->assertSame(TextDirection::LTR, TextDirection::RTL->opposite());
    }

    #[Test]
    public function it_can_be_created_from_string(): void
    {
        $this->assertSame(TextDirection::LTR, TextDirection::from('ltr'));
        $this->assertSame(TextDirection::RTL, TextDirection::from('rtl'));
    }

    #[Test]
    public function it_can_be_converted_to_string(): void
    {
        $this->assertSame('ltr', TextDirection::LTR->value);
        $this->assertSame('rtl', TextDirection::RTL->value);
    }
}
