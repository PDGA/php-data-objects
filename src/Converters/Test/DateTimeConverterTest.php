<?php

namespace PDGA\DataObjects\Converters\Test;

use DateTime;
use Exception;
use PDGA\DataObjects\Converters\DateTimeConverter;
use PHPUnit\Framework\TestCase;

class DateTimeConverterTest extends TestCase
{
    private DateTimeConverter $converter;

    public function setUp(): void
    {
        $this->converter = new DateTimeConverter();
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

    public function testOnSave()
    {
        // A DateTime object converts to an ISO8601 string.
        $this->assertSame(
            '2020-01-01T00:00:00+00:00',
            $this->converter->onSave(new DateTime('2020-01-01'))
        );
    }
}
