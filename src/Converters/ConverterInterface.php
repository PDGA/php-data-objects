<?php

namespace PDGA\DataObjects\Converters;

interface ConverterInterface
{
    public function onRetrieve(mixed $val): mixed;

    public function onSave(mixed $val): mixed;
}
