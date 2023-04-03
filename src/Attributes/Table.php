<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    private $table_name;

    public function __construct(string $name)
    {
        $this->table_name = $name;
    }

    public function getName(): string
    {
        return $this->table_name;
    }
}
