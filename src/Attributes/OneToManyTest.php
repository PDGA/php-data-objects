<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\OneToMany;

class OneToManyTest extends TestCase
{
    public function testDescription(): void
    {
        // Class is ignored here.
        $card = new OneToMany(object::class, 'fake');
        $this->assertEquals('OneToMany', $card->getDescription());
    }
}
