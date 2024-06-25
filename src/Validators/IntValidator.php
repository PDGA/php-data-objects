<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

#[Attribute]
/**
 * Validates integers and values which can be converted to integers.
 */
class IntValidator implements Validator
{
    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val)) {
            return true;
        }

        // Use native PHP integer validation.
        return filter_var($val, FILTER_VALIDATE_INT) !== false;
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be an integer.";
    }
}
