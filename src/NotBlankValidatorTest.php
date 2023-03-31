<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\NotBlankValidator;

final class NotBlankValidatorTest extends TestCase
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
    public function testEmptyStringValuePassedIn(): void
    {
        $expected_value = false;
        $result = $this->not_blank_validator->validate('');

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
        ];

        foreach ($valid_values as $value)
        {
            $this->assertTrue($this->not_blank_validator->validate($value));
        }
    }
}
