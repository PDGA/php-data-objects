<?php

namespace PDGA\DataObjects\Converters;

/**
 * Converts 'yes'/'no' values into booleans and vice versa for truthy/falsy values.
 */
class YesNoConverter implements Converter
{
    public function onRetrieve($val): bool
    {
        // Boolean of whether the value is a string 'yes'; any values other than 'yes' return false.
        return $val === 'yes';
    }

    public function onSave($val): string
    {
        // Only boolean true converts to 'yes'.
        return $val === true ? 'yes' : 'no';
    }
}
