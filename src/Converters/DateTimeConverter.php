<?php

namespace PDGA\DataObjects\Converters;

use DateTime;

/**
 * Converts a date string value into a DateTime object and vice versa.
 */
class DateTimeConverter implements Converter
{
    public function onRetrieve($val): DateTime
    {
        return new DateTime($val);
    }

    public function onSave($val): string
    {
        // Convert the DateTime object to an ISO8601 string.
        return $val->format(DateTime::ATOM);
    }
}
