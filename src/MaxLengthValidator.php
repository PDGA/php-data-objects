<?php

namespace PDGA\DataObjects;

/**
 * Validates maximum length of strings, integers and arrays.
 */
class MaxLengthValidator implements Validator
{
    private int $max_length;

    public function __construct(
        int $max_length
    )
    {
        $this->max_length = $max_length;
    }

    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val))
        {
            return true;
        }

        // Arrays must not exceed $max_length keys/indices at the first dimension.
        if (is_array($val))
        {
            return count($val) <= $this->max_length;
        }

        // Strings must not exceed $max_length characters.
        return strlen($val) <= $this->max_length;
    }

    public function getErrorMessage(string $propName): string
    {
        return "Maximum length of $propName is $this->max_length characters.";
    }
}
