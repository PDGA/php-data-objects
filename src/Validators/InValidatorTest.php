<?php

namespace PDGA\DataObjects\Validators;

use PHPUnit\Framework\TestCase;

class InValidatorTest extends TestCase
{
    private array $values = [
        1,
        'yes',
        '',
        '16'
    ];

    private InValidator $validator;

    public function setUp(): void
    {
        $this->validator = new InValidator($this->values);
    }

    public function testGetErrorMessage()
    {
        $propName = 'Property';

        $this->assertEquals(
            "$propName must be one of these values: " . implode(', ', $this->values),
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValidates()
    {
        $value = null;

        // Null values validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testIntValidates()
    {
        $value = 1;

        // Matching integer value should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testNumericStringFails()
    {
        $value = '1';

        // Numeric string matching integer value should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testTruthyIntFails()
    {
        $value = true;

        // Truthy value (true !== 1) should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testStringValidates()
    {
        $value = 'yes';

        // Matching string value should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testStringCaseFails()
    {
        $value = 'Yes';

        // Matching string value with mis-matching letter case should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testEmptyStringValidates()
    {
        $value = '';

        // Matching empty string should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testFalsyEmptyStringFails()
    {
        $value = false;

        // Falsy value (false !== '') should not validate.
        $this->assertFalse($this->validator->validate($value));
    }

    public function testNumericStringValidates()
    {
        $value = '16';

        // Value matching a numeric string should validate.
        $this->assertTrue($this->validator->validate($value));
    }

    public function testIntAsNumericStringFails()
    {
        $value = 16;

        // Integer value matching a numeric string should not validate.
        $this->assertFalse($this->validator->validate($value));
    }
}
