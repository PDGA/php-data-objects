<?php

namespace PDGA\DataObjects\Converters\Test;

use PDGA\DataObjects\Converters\EmptyStringToNullConverter;
use PHPUnit\Framework\TestCase;

class EmptyStringToNullConverterTest extends TestCase
{
    private EmptyStringToNullConverter $converter;

    public function setUp(): void
    {
        $this->converter = new EmptyStringToNullConverter();
    }

    public function testOnRetrieveEmptyIsConvertedToNull()
    {
        $empty = '';

        $result = $this->converter->onRetrieve($empty);

        $this->assertNull($result);
    }

    public function testOnRetrieveNullReturnsNull()
    {
        $value = null;

        $result = $this->converter->onRetrieve($value);

        $this->assertNull($result);
    }

    public function testOnRetrieveNonEmptyStringReturnsString()
    {
        $value = 'test';

        $result = $this->converter->onRetrieve($value);

        $this->assertEquals($value, $result);
    }

    public function testOnSaveEmptyIsConvertedToNull()
    {
        $empty = '';

        $result = $this->converter->onSave($empty);

        $this->assertNull($result);
    }

    public function testOnSaveNullReturnsNull()
    {
        $value = null;

        $result = $this->converter->onSave($value);

        $this->assertNull($result);
    }

    public function testOnSaveNonEmptyStringReturnsString()
    {
        $value = 'test';

        $result = $this->converter->onSave($value);

        $this->assertEquals($value, $result);
    }
}
