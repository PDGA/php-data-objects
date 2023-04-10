<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

use PDGA\DataObjects\Validators\Validator;

#[Attribute]
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

        if (is_string($val) && empty(trim($val)))
        {
            return false;
        }

        if (isset($val))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be
     * not blank.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * that the property should be not blank.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must not be blank.";
    }
}
