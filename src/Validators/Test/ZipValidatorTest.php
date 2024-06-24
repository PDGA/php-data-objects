<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\ZipValidator;
use PHPUnit\Framework\TestCase;

class ZipValidatorTest extends TestCase
{
    private $zip_validator;

    protected function setUp(): void
    {
        $this->zip_validator = new ZipValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The zip field must not be an array and must be no longer than 15 characters.";
        $result = $this->zip_validator->getErrorMessage("zip");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->zip_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Arrays should result in false.
     */
    public function testArrayValuesPassedIn(): void
    {
        $invalid_array = ['2', '3'];

        $this->assertFalse($this->zip_validator->validate($invalid_array));
    }

    /**
     * Invalid values should result in false.
     */
    public function testInvalidValues(): void
    {
        $invalid_values = [
            'this is too long',
            123456789101112131415,
        ];

        foreach ($invalid_values as $value) {
            $this->assertFalse($this->zip_validator->validate($value));
        }
    }

    /**
     * Valid values should result in true.
     */
    public function testValidValues(): void
    {
        $valid_values = [
            'this is fine',
            123456,
            true,
            0,
            false,
        ];

        foreach ($valid_values as $value) {
            $this->assertTrue($this->zip_validator->validate($value));
        }
    }
}
