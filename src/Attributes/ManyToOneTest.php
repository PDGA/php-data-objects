<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\ManyToOne;

class ManyToOneTest extends TestCase
{
    public function testDescription(): void
    {
        // Classes are ignored here.
        $card = new ManyToOne(object::class, object::class);
        $this->assertEquals('ManyToOne', $card->describe());
    }
}
