<?php

namespace PDGA\DataObjects\Converters;

interface Converter
{
    public function onRetrieve(mixed $val): mixed;

    public function onSave(mixed $val): mixed;
}
