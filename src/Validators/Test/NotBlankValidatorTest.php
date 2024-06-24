<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\NotBlankValidator;
use PHPUnit\Framework\TestCase;

class NotBlankValidatorTest extends TestCase
{
    private $not_blank_validator;

    protected function setUp(): void
    {
        $this->not_blank_validator = new NotBlankValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The variable field must not be blank.";
        $result = $this->not_blank_validator->getErrorMessage("variable");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->not_blank_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Empty string values should result in false.
     */
    public function testInvalidStringValuesPassedIn(): void
    {
        $invalid_values = [
            '',
            ' ',
        ];

        foreach ($invalid_values as $value) {
            $this->assertFalse($this->not_blank_validator->validate($value));
        }
    }

    /**
     * A defined value should result in true.
     */
    public function testDefinedValues(): void
    {
        $valid_values = [
            'this is a string',
            123456,
            true,
            0,
            false,
        ];

        foreach ($valid_values as $value) {
            $this->assertTrue($this->not_blank_validator->validate($value));
        }
    }
}
