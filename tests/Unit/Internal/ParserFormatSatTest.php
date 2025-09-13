<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Internal\ParserFormatSAT;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ParserFormatSatTest extends TestCase
{
    public function testWithEmptySource(): void
    {
        $parser = new ParserFormatSAT();
        $this->assertSame([], $parser->getFormValues(''));
    }

    public function testWithExactSource(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__LASTFOCUS' => 'x-last-focus',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function testWithSourceMissingKeys(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__FOO_BAR' => 'foo-bar',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        unset($data['__FOO_BAR']);
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function testWithSourceOtherKeys(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function testWithSourceWithLeadText(): void
    {
        $data = [
            '__LASTFOCUS' => 'x-foo',
            '__VIEWSTATE' => 'x-bar',
        ];
        $source = 'prefixed text' . $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        $this->assertSame($data, $parser->getFormValues($source));
    }

    /**
     * @param array<string, string> $values
     */
    public function buildFakeSourceData(array $values): string
    {
        return implode('', array_map(
            fn ($fieldName, $fieldValue): string => '|' . implode('|', [strlen($fieldValue), 'hiddenField', $fieldName, $fieldValue]),
            array_keys($values),
            $values,
        ));
    }
}
