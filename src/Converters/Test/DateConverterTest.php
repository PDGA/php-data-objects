<?php

namespace PDGA\DataObjects\Converters\Test;

use DateTime;
use Exception;
use PDGA\DataObjects\Converters\DateConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateConverterTest extends TestCase
{
    private DateConverter $converter;

    public function setUp(): void
    {
        $this->converter = new DateConverter();
    }

    public function testOnRetrieve()
    {
        // A date string converts to a DateTime object.
        $this->assertEquals(
            new DateTime('2020-01-01'),
            $this->converter->onRetrieve('2020-01-01')
        );
    }

    public function testOnRetrieveWithBadString()
    {
        // A non-date string results in an exception.
        $this->expectException(Exception::class);
        $this->converter->onRetrieve('This is a bad string');
    }

    #[DataProvider('validDateProvider')]
    public function testOnSave($expected_date_string, DateTime $original_date)
    {
        // A DateTime object converts to an ISO8601 string.
        $this->assertSame(
            $expected_date_string,
            $this->converter->onSave($original_date)
        );
    }

    public static function validDateProvider()
    {
        return [
            'basic' => [
                '1950-01-02',
                new DateTime('1950-01-02')
            ],
            'turn of century' => [
                '1900-01-02',
                new DateTime('1900-01-02')
            ],
            'future' => [
                '3000-01-02',
                new DateTime('3000-01-02')
            ],
            'includes time component' => [
                '1976-01-01',
                new DateTime('1976-01-01T00:00:00+00:00')
            ],
        ];
    }
}
