<?php

namespace PDGA\DataObjects\Converters;

/**
 * Converts a text/blob column containing serialized
 * JSON data into a PHP array.
 */
class JsonConverter implements Converter
{
    public function onRetrieve($val): array
    {
        return json_decode($val, true);
    }

    public function onSave($val): string
    {
        return json_encode($val);
    }
}
