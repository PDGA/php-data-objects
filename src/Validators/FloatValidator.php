<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

use PDGA\DataObjects\Validators\Validator;

#[Attribute]
/**
 * Validates floats and values which can be converted to floats.
 */
class FloatValidator implements Validator
{
    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val))
        {
            return true;
        }

        // Use native PHP float validation.
        return filter_var($val, FILTER_VALIDATE_FLOAT) !== false;
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be a float.";
    }
}
