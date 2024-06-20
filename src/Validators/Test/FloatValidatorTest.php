<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\FloatValidator;
use PHPUnit\Framework\TestCase;

class FloatValidatorTest extends TestCase
{
    private FloatValidator $validator;

    public function setUp(): void
    {
        $this->validator = new FloatValidator();
    }

    public function testGetErrorMessage()
    {
        $propName = 'Property';

        $this->assertEquals(
            "$propName must be a float.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValue()
    {
        $value = null;

        // A null value should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testFloatValidates()
    {
        $value = 3.14;

        // A float should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNegativeFloatValidates()
    {
        $value = -3.14;

        // A negative float should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testIntValidates()
    {
        $value = 9;

        // An integer value should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringValidates()
    {
        $value = "3.14";

        // A numeric string should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringWithWhitespaceValidates()
    {
        $value = " 3.14 ";

        // A numeric string with leading/trailing whitespace should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringWithInvalidWhitespaceFails()
    {
        $value = "3. 1 4 ";

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
