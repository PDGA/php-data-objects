<?php

namespace PDGA\DataObjects\Models\Test;

class ModelInstantiatorTestDataObject
{
    public function __construct(
        public string $prop = 'default',
    ) {
        $this->propertyForcesException = $prop;
    }
    public mixed $propertyForcesException;
}