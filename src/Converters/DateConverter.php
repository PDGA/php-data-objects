<?php

namespace PDGA\DataObjects\Converters;

use DateTime;

/**
 * Converts a date string value into a DateTime object and vice versa.
 */
class DateConverter implements Converter
{
    public function onRetrieve($val): DateTime
    {
        return new DateTime($val);
    }

    public function onSave($val): string
    {
        return $val->format('Y-m-d');
    }
}
