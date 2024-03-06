<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\Column;

class Address
{
    #[Column('Number')]
    public int $number;
    #[Column('Street')]
    public string $street;
}
