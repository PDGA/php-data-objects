<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

#[Attribute]
/**
 * Validates minimum length of strings, integers and arrays.
 */
class MinLengthValidator implements Validator
{
    private int $min_length;

    public function __construct(
        int $min_length
    ) {
        $this->min_length = $min_length;
    }

    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val)) {
            return true;
        }

        // Arrays must contain at least $min_length keys/indices at the first dimension.
        if (is_array($val)) {
            return count($val) >= $this->min_length;
        }

        // Strings must meet or exceed $min_length characters.
        return strlen($val) >= $this->min_length;
    }

    public function getErrorMessage(string $propName): string
    {
        return "Minimum length of $propName is $this->min_length characters.";
    }
}
