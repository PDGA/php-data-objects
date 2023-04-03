<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;

class ZipValidator implements Validator
{
    /**
     * Validates that the passed in value is null or at most 15 characters long.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is not null.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val))
        {
            return true;
        }

        if (is_array($val))
        {
            return false;
        }

        if (strlen($val) <= 15)
        {
            return true;
        }

        return false;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be
     * no longer than 15 characters long.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * the valid type the property should be.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be no longer than 15 characters.";
    }
}
