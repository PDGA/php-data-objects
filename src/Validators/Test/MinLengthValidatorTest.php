<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\MinLengthValidator;
use PHPUnit\Framework\TestCase;

class MinLengthValidatorTest extends TestCase
{
    private MinLengthValidator $validator;

    /**
     * All tests are against a min length of 5.
     * @var int
     */
    private int $min_length = 5;

    public function setUp(): void
    {
        $this->validator = new MinLengthValidator($this->min_length);
    }

    public function testGetErrorMessage()
    {
        $propName = 'Property';

        $this->assertEquals(
            "Minimum length of $propName is $this->min_length characters.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValue()
    {
        // Use a null value
        $value = null;

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testStringExceedsMinLength()
    {
        // Use a string value longer than 5 characters.
        $value = "longerthanfive";

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testStringEqualsMinLength()
    {
        // Use a string of 5 characters.
        $value = "bang!";

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testStringUnderMinLength()
    {
        // Use a string of 4 characters.
        $value = "four";

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testIntegerExceedsMinLength()
    {
        // Use an integer longer than 5 characters.
        $value = 123456;

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testIntegerEqualsMinLength()
    {
        // Use an integer of 5 characters.
        $value = 12345;

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testIntegerUnderMinLength()
    {
        // Use an integer shorter than 5 characters.
        $value = 1234;

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testArrayExceedsMinLength()
    {
        // Use an array of more than 5 items.
        $value = [1, 2, 3, 4, 5, 6];

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testArrayEqualsMinLength()
    {
        // Use an array of 5 items.
        $value = [1, 2, 3, 4, 5];

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testArrayUnderMinLength()
    {
        // Use an array of fewer than 5 items.
        $value = [1, 2, 3, 4];

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testMultiDimensionalArrayUnderMinLength()
    {
        // Use an array of fewer than 5 items at the first dimension
        // with more than 5 items total in all dimensions.
        $value = [1, 2, 3, 4 => [5, 6, 7, 8]];

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }
}
