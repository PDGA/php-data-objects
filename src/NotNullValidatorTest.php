<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\NotNullValidator;

class NotNullValidatorTest extends TestCase
{
    private $not_null_validator;

    protected function setUp(): void
    {
        $this->not_null_validator = new NotNullValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The variable field must not be null.";
        $result = $this->not_null_validator->getErrorMessage("variable");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in false.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = false;
        $result = $this->not_null_validator->validate(null);

        $this->assertSame($expected_value, $result);
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
            '',
            ' ',
        ];

        foreach ($valid_values as $value)
        {
            $this->assertTrue($this->not_null_validator->validate($value));
        }
    }
}
