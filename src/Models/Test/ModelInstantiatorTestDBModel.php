<?php

namespace PDGA\DataObjects\Models\Test;

// Mimics an Eloquent model, which has an attributes array.
class ModelInstantiatorTestDBModel
{
    private $attributes = ['PDGANum' => 123];

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}