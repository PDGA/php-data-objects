<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;

class SequentialArrayValidator implements Validator
{
    /**
     * Validates that the passed in value is null, or is undefined,
     * or is an array with sequential numeric keys which start at zero.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is null, undefined, or
     * an array with sequential numeric keys that start at zero.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val))
        {
            return true;
        }

        if (!is_array($val))
        {
            return false;
        }

        // If the array has string based keys return false.
        if (count(array_filter(array_keys($val), 'is_string')) > 0)
        {
            return false;
        }

        // If the numerical array keys are out of order return false.
        if (array_keys($val) !== range(0, count($val) - 1))
        {
            return false;
        }

        return true;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be a
     * sequential (non-associative) zero-indexed array.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * the valid type the property should be.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be a sequential (non-associative) zero-indexed array.";
    }
}
