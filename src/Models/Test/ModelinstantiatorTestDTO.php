<?php

namespace PDGA\DataObjects\Models\Test;

class ModelinstantiatorTestDTO
{
    public function __construct(
        public string $prop = 'default',
    ) {
        $this->propertyForcesException = $prop;
    }
    public object $propertyForcesException;
}