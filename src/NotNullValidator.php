<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;

class NotNullValidator implements Validator
{
    /**
     * Validates that the passed in value is not null.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is not null.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val))
        {
            return false;
        }

        return true;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be
     * not null.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * that the property should not be null.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must not be null.";
    }
}
