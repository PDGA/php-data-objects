<?php

namespace PDGA\DataObjects\Validators;

/**
 * Validates phone numbers.
 *
 * Phone numbers with extensions do not validate.
 */
class PhoneValidator implements Validator
{
    public function validate(mixed $val): bool
    {
        // Null values validate
        if (is_null($val))
        {
            return true;
        }

        // Arrays do not validate.
        if (is_array($val))
        {
            return false;
        }

        // Value can contain only numbers, "(", ")" "-", "+", "." and whitespace characters.
        // This excludes extensions (e.g. "x123", "Ext. 456") from validation.
        if (preg_match('/[^\d\(\)\-\+\s\.]/', $val))
        {
            return false;
        }

        // Count the number of numeric characters in the value.
        $numeric_chars = strlen(preg_replace('/\D/', '', $val));

        // Value must contain between 10 and 15 digits.
        if ($numeric_chars < 10 || $numeric_chars > 15)
        {
            return false;
        }

        return true;
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be a valid phone number.";
    }
}
