<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

use PDGA\DataObjects\Validators\Validator;

#[Attribute]
class BoolValidator implements Validator
{
    /**
     * Validates that the passed in value is null or undefined,
     * or is a bool.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is null, undefined, or
     * a bool.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val)) {
            return true;
        }

        if (!is_bool($val)) {
            return false;
        }

        return true;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be a
     * bool.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * the valid type the property should be.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be a bool.";
    }
}
