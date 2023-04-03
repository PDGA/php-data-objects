<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Validators\DateValidator;

final class DateValidatorTest extends TestCase
{
    private $date_validator;

    protected function setUp(): void
    {
        $this->date_validator = new DateValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The date field must be a DateTime or a string in ISO8601 date format.";
        $result = $this->date_validator->getErrorMessage("date");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->date_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Undefined values should result in true.
     */
    public function testUndefinedPassedIn(): void
    {
        $value;
        $expected_value = true;
        $result = $this->date_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Non-date values should result in false.
     */
    public function testNonDateValuePassedIn(): void
    {
        $expected_value = false;
        $result = $this->date_validator->validate(1234);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings that aren't ISO strings should result in false.
     */
    public function testNonIsoStringPassedIn(): void
    {
        $value = "test";
        $expected_value = false;
        $result = $this->date_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings in ISO8601 format for but don't contain valid dates should result in false.
     */
    public function testIsoStringForInvalidDatePassedIn(): void
    {
        $value = "2023-15-31T23:25:42Z";
        $expected_value = false;
        $result = $this->date_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Strings in ISO8601 format for valid dates should result in true.
     */
    public function testValidISOString(): void
    {
        $value = "2023-03-30T23:25:42Z";
        $expected_value = true;
        $result = $this->date_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * DateTimes should result in true.
     */
    public function testValidDateTime(): void
    {
        $value = new \DateTime();
        $expected_value = true;
        $result = $this->date_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }
}
