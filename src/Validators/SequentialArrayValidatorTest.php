<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\SequentialArrayValidator;

final class SequentialArrayValidatorTest extends TestCase
{
    private $array_validator;

    protected function setUp(): void
    {
        $this->array_validator = new SequentialArrayValidator();
    }

    /**
     * Make sure the error message returned is as expected.
     */
    public function testGetErrorMessage(): void
    {
        $expected_value = "The array field must be a sequential (non-associative) zero-indexed array.";
        $result = $this->array_validator->getErrorMessage("array");

        $this->assertSame($expected_value, $result);
    }

    /**
     * Nulls should result in true.
     */
    public function testNullPassedIn(): void
    {
        $expected_value = true;
        $result = $this->array_validator->validate(null);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Undefined values should result in true.
     */
    public function testUndefinedPassedIn(): void
    {
        $value;
        $expected_value = true;
        $result = $this->array_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Non-array values should result in false.
     */
    public function testNonArrayValuePassedIn(): void
    {
        $expected_value = false;
        $result = $this->array_validator->validate(1234);

        $this->assertSame($expected_value, $result);
    }

    /**
     * Associative arrays should result in false.
     */
    public function testAssociativeArrayPassedIn(): void
    {
        $value = ["test" => "value"];
        $expected_value = false;
        $result = $this->array_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * An array with numeric keys but out of sequence should result in false.
     */
    public function testArrayOutOfSequence(): void
    {
        $value = ["1" => 'a', "0" => 'b', "2" => 'c'];
        $expected_value = false;
        $result = $this->array_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }

    /**
     * A sequential array should result in true.
     */
    public function testValidArray(): void
    {
        $value = ["0" => 'a', "1" => 'b', "2" => 'c'];
        $expected_value = true;
        $result = $this->array_validator->validate($value);

        $this->assertSame($expected_value, $result);
    }
}
