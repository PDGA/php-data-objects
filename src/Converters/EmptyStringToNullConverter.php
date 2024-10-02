<?php

namespace PDGA\DataObjects\Converters;

/**
 * Converts empty strings into null values.
 * This should be used with nullable string data object properties.
 */
class EmptyStringToNullConverter implements Converter
{
    public function onRetrieve(mixed $val): mixed
    {
        return $this->convertEmptyStringToNull($val);
    }

    public function onSave(mixed $val): mixed
    {
        return $this->convertEmptyStringToNull($val);
    }

    private function convertEmptyStringToNull($val): ?string
    {
        return $val != '' ? $val : null;
    }
}
