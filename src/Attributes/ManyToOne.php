<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

use PDGA\DataObjects\Attributes\Cardinality;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne extends Cardinality
{
    /**
     * Describe the relationship from left to right.
     *
     * @return string Always "ManyToOne"
     */
    public function describe(): string
    {
        return "ManyToOne";
    }
}
