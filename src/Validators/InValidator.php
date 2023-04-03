<?php

namespace PDGA\DataObjects\Validators;

/**
 * Validates that a value is within an array. Uses strict comparison: types, value and case must match.
 */
class InValidator implements Validator
{
    private array $valid_values;

    public function __construct(
        array $valid_values
    )
    {
        $this->valid_values = $valid_values;
    }

    public function validate(mixed $val): bool
    {
        // Null values validate.
        if (is_null($val))
        {
            return true;
        }

        // Find the item in the $values array with strict comparison.
        return in_array($val, $this->valid_values, true);
    }

    public function getErrorMessage(string $propName): string
    {
        return "$propName must be one of these values: " . implode(', ', $this->valid_values);
    }
}
