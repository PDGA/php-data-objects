<?php

namespace PDGA\DataObjects\Validators;

use \DateTime;
use \Exception;
use Attribute;

use PDGA\DataObjects\Validators\Validator;

#[Attribute]
class ISO8601Validator implements Validator
{
    /**
     * Validates that the passed in value is null or undefined,
     * or is a string in ISO8601 date format.
     *
     * @param mixed $val The value to validate.
     * @return bool Returns true if the passed in value is null, undefined, or
     * a string in ISO8601 date format.
     */
    public function validate(mixed $val): bool
    {
        if (is_null($val)) {
            return true;
        }

        if (!is_string($val)) {
            return false;
        }

        if (!preg_match('/^'.
            '(\d{4}-\d{2}-\d{2})(?:T'. // YYYY-MM-DDT ex: 2014-01-01T
            '\d{2}:\d{2}:\d{2}'.  // HH:MM:SS  ex: 17:00:00
            '(?:Z|(?:[-|\+]\d{2}:\d{2})))?'.  // Z or +01:00 or -01:00
            '$/', $val, $matches)) {
            return false;
        }

        try {
            $format = 'Y-m-d';
            $date_check = new DateTime($val);
            // If the date created by DateTime doesn't match the date part of the value
            // passed in then the value date is not valid.
            if ($date_check->format($format) == $matches[1]) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            // Invalid dates (ie month of 15+ or day of 32+ etc) will result in en exception.
            return false;
        }
    }

    /**
     * Returns an error indicating that the passed in property name is supposed to be a
     * string in ISO8601 format.
     *
     * @param string $propName The name of the property.
     * @return string Returns an error string which includes the name of the property and
     * the valid type the property should be.
     */
    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be a string in ISO8601 date format.";
    }
}
