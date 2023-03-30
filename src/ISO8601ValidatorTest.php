<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\ISO8601Validator;

final class ISO8601ValidatorTest extends TestCase
{
    private $iso_validator;

    protected function setUp(): void
    {
        $this->iso_validator = new ISO8601Validator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The iso_string field must be a string in ISO8601 date format.";
        $result = $this->iso_validator->getErrorMessage("iso_string");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->iso_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Undefined values should result in true.
     */
    public function testUndefinedPassedIn(): void
    {
        $value;
        $expected_value = true;
        $result = $this->iso_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Non-string values should result in false.
     */
    public function testNonStringValuePassedIn(): void
    {
        $expected_value = false;
        $result = $this->iso_validator->validate(1234);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings that aren't ISO strings should result in false.
     */
    public function testNonIsoStringPassedIn(): void
    {
        $value = "test";
        $expected_value = false;
        $result = $this->iso_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings in ISO8601 format for but don't contain valid dates should result in false.
     */
    public function testIsoStringForInvalidDatePassedIn(): void
    {
        $value = "2023-15-31T23:25:42Z";
        $expected_value = false;
        $result = $this->iso_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings in ISO8601 format for valid dates should result in true.
     */
    public function testValidArray(): void
    {
        $value = "2023-03-30T23:25:42Z";
        $expected_value = true;
        $result = $this->iso_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }
}
