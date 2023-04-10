<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

#[Attribute]
/**
 * Validates that a given value is a string.
 */
class StringValidator implements Validator
{
    public function validate(mixed $val): bool
    {
        // Null validates.
        if (is_null($val))
        {
            return true;
        }

        return is_string($val);
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be a string.";
    }
}
