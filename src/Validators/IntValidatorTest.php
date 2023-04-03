<?php

namespace PDGA\DataObjects;

use PHPUnit\Framework\TestCase;

class IntValidatorTest extends TestCase
{
    private IntValidator $validator;

    public function setUp(): void
    {
        $this->validator = new IntValidator();
    }

    public function testGetErrorMessage()
    {
        $propName = 'Property';

        $this->assertEquals(
            "$propName must be an integer.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValue()
    {
        $value = null;

        // A null value should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testIntValidates()
    {
        $value = 24472;

        // An integer should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNegativeIntValidates()
    {
        $value = -9000;

        // A negative integer should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testDecimalFails()
    {
        $value = 9.2;

        // A decimal value should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testNumericStringValidates()
    {
        $value = "10";

        // A numeric string should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringWithWhitespaceValidates()
    {
        $value = " 10 ";

        // A numeric string with leading/trailing whitespace should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringWithInvalidWhitespaceFails()
    {
        $value = "1 0";

        // A numeric string with interior whitespace should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testNonNumericStringFails()
    {
        $value = "10b";

        // A non-numeric string should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testArrayWithIntegerFails()
    {
        $value = [1];

        // An array with a single integer element should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testEmptyStringFails()
    {
        $value = '';

        // Empty string should not validate.
        $this->assertFalse($this->validator->validate($value));
    }
}
