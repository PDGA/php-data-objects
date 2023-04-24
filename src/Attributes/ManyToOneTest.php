<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\ManyToOne;

class ManyToOneTest extends TestCase
{
    public function testDescription(): void
    {
        // Class is ignored here.
        $card = new ManyToOne(object::class);
        $this->assertEquals('ManyToOne', $card->getDescription());
    }
}
