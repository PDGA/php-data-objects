<?php

namespace PDGA\DataObjects;

use PDGA\DataObjects\Validator;

class SequentialArrayValidator implements Validator
{
    public function __construct() {

    }

    public function validate(mixed $val): bool
    {
        if (is_null($val) || !isset($val))
        {
            return true;
        }

        if (!is_array($val))
        {
            return false;
        }

        // If the array has string based keys return false.
        if (count(array_filter(array_keys($val), 'is_string')) > 0)
        {
            return false;
        }

        // If the numerical array keys are out of order return false.
        if (array_keys($val) !== range(0, count($val) - 1))
        {
            return false;
        }

        return true;
    }

    public function getErrorMessage(string $propName): string
    {
        return "The $propName field must be a sequential (non-associative) array.";
    }
}
