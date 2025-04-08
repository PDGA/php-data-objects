<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\PhoneValidator;
use PHPUnit\Framework\TestCase;

class PhoneValidatorTest extends TestCase
{
    private PhoneValidator $validator;

    public function setUp(): void
    {
        $this->validator = new PhoneValidator();
    }

    public function testGetErrorMessage()
    {
        $propName = 'Phone number';

        $this->assertSame(
            "$propName must be a valid phone number.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValidates()
    {
        $value = null;

        $this->assertTrue($this->validator->validate($value));
    }

    public function testArrayFails()
    {
        $value = ['123', '456', '7890'];

        $this->assertFalse($this->validator->validate($value));
    }

    /**
     * Tests a list of valid phone numbers.
     *
     * @return void
     */
    public function testValidPhoneNumbersValidate(): void
    {
        foreach (
            [
             17184441122,
             7184441122,
             '1-718-444-1122',
             '718-444-1122',
             '(718)-444-1122',
             '17184441122',
             '7184441122',
             '718.444.1122',
             '1718.444.1122',
             '1-123-456-7890',
             '1 123-456-7890',
             '1 (123) 456-7890',
             '1 123 456 7890',
             '1.123.456.7890',
             '+91 (123) 456-7890',
             '18005551234',
             '1 800 555 1234',
             '+1 800 555-1234',
             '+86 800 555 1234',
             '+86 800 555 1234 567', // Maximum of 15 characters.
            ] as $valid_phone_number
        ) {
            $this->assertTrue($this->validator->validate($valid_phone_number));
        }
    }

    /**
     * Tests a list of invalid phone numbers.
     *
     * @return void
     */
    public function testInvalidPhoneNumbersFail(): void
    {
        foreach (
            [
             '12 345 6789', // Fewer than 10 numeric characters.
             '123 456 789 10111213', // More than 15 characters.
             '123 45a 67890', // Invalid alpha character.
             '123 456 7890 x234', // Invalid alpha character (extensions are disallowed).
             '123 456 7890 Ext. 234', // Invalid alpha characters (extensions are disallowed).
             ] as $invalid_phone_number
        ) {
            $this->assertFalse($this->validator->validate($invalid_phone_number));
        }
    }
}
