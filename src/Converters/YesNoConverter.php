<?php

namespace PDGA\DataObjects\Converters;

/**
 * Converts 'yes'/'no' values into booleans and vice versa for truthy/falsy values.
 */
class YesNoConverter implements ConverterInterface
{
    public function onRetrieve(mixed $val): bool
    {
        // Boolean of whether the value is a string 'yes'; any values other than 'yes' return false.
        return $val === 'yes';
    }

    public function onSave(mixed $val): string
    {
        // Truthy values convert to 'yes'.
        return $val ? 'yes' : 'no';
    }
}
