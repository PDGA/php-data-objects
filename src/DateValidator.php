<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;
use PDGA\DataObjects\ISO8601Validator;

class DateValidator implements Validator
{
    /**
     * Validates that the passed in value is null or undefined,
     * or is a DateTime or string in ISO8601 date format.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is null, undefined, or
     * a DateTime or a string in ISO8601 date format.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val))
        {
            return true;
        }

        if (is_string($val))
        {
            $iso_validator = new ISO8601Validator();
            return $iso_validator->validate($val);
        }

        if (is_a($val, 'DateTime'))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be a
     * DateTime or string in ISO8601 format.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * the valid type the property should be.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be a DateTime or a string in ISO8601 date format.";
    }
}
