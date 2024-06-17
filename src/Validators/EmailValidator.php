<?php

namespace PDGA\DataObjects\Validators;

use Attribute;

#[Attribute]
/**
 * Validates email addresses. Whitespace does not validate.
 */
class EmailValidator implements Validator
{
    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val)) {
            return true;
        }

        // Use native PHP email validation.
        return filter_var($val, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be an email address.";
    }
}
