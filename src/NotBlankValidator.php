<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;

class NotBlankValidator implements Validator
{
    /**
     * Validates that the passed in value is null or not blank.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is null or not blank.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val))
        {
            return true;
        }

        return !empty($val);
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
        return "The $propName field must not be blank.";
    }
}
