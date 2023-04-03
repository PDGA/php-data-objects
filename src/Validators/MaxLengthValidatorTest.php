<?php

namespace PDGA\DataObjects\Validators;

use PHPUnit\Framework\TestCase;

class MaxLengthValidatorTest extends TestCase
{
    private MaxLengthValidator $validator;

    /**
     * All tests are against a max length of 5.
     * @var int
     */
    private int $max_length = 5;

    public function setUp(): void
    {
        $this->validator = new MaxLengthValidator($this->max_length);
    }

    public function testGetErrorMessage()
    {
        $propName = 'Property';

        $this->assertEquals(
            "Maximum length of $propName is $this->max_length characters.",
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

    public function testStringExceedsMaxLength()
    {
        // Use a string value longer than 5 characters.
        $value = "longerthanfive";

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testStringEqualsMaxLength()
    {
        // Use a string of 5 characters.
        $value = "bang!";

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testStringUnderMaxLength()
    {
        // Use a string of 4 characters.
        $value = "four";

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testIntegerExceedsMaxLength()
    {
        // Use an integer longer than 5 characters.
        $value = 123456;

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testIntegerEqualsMaxLength()
    {
        // Use an integer of 5 characters.
        $value = 12345;

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testIntegerUnderMaxLength()
    {
        // Use an integer shorter than 5 characters.
        $value = 1234;

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testArrayExceedsMaxLength()
    {
        // Use an array of more than 5 items.
        $value = [1, 2, 3, 4, 5, 6];

        // The value should not validate.
        $this->assertEquals(false, $this->validator->validate($value));
    }

    public function testArrayEqualsMaxLength()
    {
        // Use an array of 5 items.
        $value = [1, 2, 3, 4, 5];

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testArrayUnderMaxLength()
    {
        // Use an array of fewer than 5 items.
        $value = [1, 2, 3, 4];

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }

    public function testMultiDimensionalArrayUnderMaxLength()
    {
        // Use an array of fewer than 5 items at the first dimension
        // with more than 5 items total in all dimensions.
        $value = [1, 2, 3, 4 => [5, 6, 7, 8]];

        // The value should validate.
        $this->assertEquals(true, $this->validator->validate($value));
    }
}
