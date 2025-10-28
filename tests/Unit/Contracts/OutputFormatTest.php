<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Contracts;

use FormGenerator\Tests\TestCase;
use FormGenerator\V2\Contracts\OutputFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OutputFormat::class)]
class OutputFormatTest extends TestCase
{
    #[Test]
    public function it_has_html_case(): void
    {
        $format = OutputFormat::HTML;

        $this->assertInstanceOf(OutputFormat::class, $format);
        $this->assertSame('html', $format->value);
    }

    #[Test]
    public function it_has_json_case(): void
    {
        $format = OutputFormat::JSON;

        $this->assertInstanceOf(OutputFormat::class, $format);
        $this->assertSame('json', $format->value);
    }

    #[Test]
    public function it_has_xml_case(): void
    {
        $format = OutputFormat::XML;

        $this->assertInstanceOf(OutputFormat::class, $format);
        $this->assertSame('xml', $format->value);
    }

    #[Test]
    public function it_returns_correct_content_type_for_html(): void
    {
        $this->assertSame('text/html', OutputFormat::HTML->getContentType());
    }

    #[Test]
    public function it_returns_correct_content_type_for_json(): void
    {
        $this->assertSame('application/json', OutputFormat::JSON->getContentType());
    }

    #[Test]
    public function it_returns_correct_content_type_for_xml(): void
    {
        $this->assertSame('application/xml', OutputFormat::XML->getContentType());
    }

    #[Test]
    public function it_returns_correct_extension_for_html(): void
    {
        $this->assertSame('html', OutputFormat::HTML->getExtension());
    }

    #[Test]
    public function it_returns_correct_extension_for_json(): void
    {
        $this->assertSame('json', OutputFormat::JSON->getExtension());
    }

    #[Test]
    public function it_returns_correct_extension_for_xml(): void
    {
        $this->assertSame('xml', OutputFormat::XML->getExtension());
    }

    #[Test]
    public function it_can_be_created_from_string(): void
    {
        $this->assertSame(OutputFormat::HTML, OutputFormat::from('html'));
        $this->assertSame(OutputFormat::JSON, OutputFormat::from('json'));
        $this->assertSame(OutputFormat::XML, OutputFormat::from('xml'));
    }

    #[Test]
    public function it_can_be_converted_to_string(): void
    {
        $this->assertSame('html', OutputFormat::HTML->value);
        $this->assertSame('json', OutputFormat::JSON->value);
        $this->assertSame('xml', OutputFormat::XML->value);
    }
}
