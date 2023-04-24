<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

use PDGA\DataObjects\Attributes\Cardinality;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany extends Cardinality
{
    /**
     * Describe the relationship from left to right.
     *
     * @return string Always "OneToMany"
     */
    public function getDescription(): string
    {
        return "OneToMany";
    }
}
