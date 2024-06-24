<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\StringValidator;
use PHPUnit\Framework\TestCase;

class StringValidatorTest extends TestCase
{
    private StringValidator $validator;

    public function setUp(): void
    {
        $this->validator = new StringValidator();
    }

    public function testNullValidates()
    {
        $value = null;

        $this->assertTrue($this->validator->validate($value));
    }

    public function testGetErrorMessage()
    {
        $propName = "String Field";

        $this->assertSame(
            "$propName must be a string.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testStringValidates()
    {
        $value = 'a string';

        $this->assertTrue($this->validator->validate($value));
    }

    public function testIntFails()
    {
        $value = 123;

        $this->assertFalse($this->validator->validate($value));
    }

    public function testArrayFails()
    {
        $value = ['string'];

        $this->assertFalse($this->validator->validate($value));
    }

    public function testBooleanFails()
    {
        $value = true;

        $this->assertFalse($this->validator->validate($value));
    }

    public function testObjectFails()
    {
        $value = new \stdClass();

        $this->assertFalse($this->validator->validate($value));
    }
}
