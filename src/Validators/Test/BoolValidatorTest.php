<?php

namespace PDGA\DataObjects\Validators\Test;

use PDGA\DataObjects\Validators\BoolValidator;
use PHPUnit\Framework\TestCase;

class BoolValidatorTest extends TestCase
{
    private $bool_validator;

    protected function setUp(): void
    {
        $this->bool_validator = new BoolValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The bool field must be a bool.";
        $result = $this->bool_validator->getErrorMessage("bool");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->bool_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Non-bool values should result in false.
     */
    public function testNonBoolValuePassedIn(): void
    {
        $expected_value = false;
        $result = $this->bool_validator->validate(1234);

        $this->assertSame($expected_value, $result);
    }

    /**
     * A bool should result in true.
     */
    public function testValidBool(): void
    {
        $value = false;
        $expected_value = true;
        $result = $this->bool_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }
}
