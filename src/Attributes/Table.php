<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    private $name;

    /**
     * Constructor for the Table attribute.
     *
     * @param string $name - The name of the table.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the table.
     *
     * @return string Returns the name of the table.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
