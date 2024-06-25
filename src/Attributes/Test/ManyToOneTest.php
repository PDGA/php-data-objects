<?php

namespace PDGA\DataObjects\Attributes\Test;

use PDGA\DataObjects\Attributes\ManyToOne;
use PHPUnit\Framework\TestCase;

class ManyToOneTest extends TestCase
{
    public function testDescription(): void
    {
        // Class is ignored here.
        $card = new ManyToOne(object::class, 'fake');
        $this->assertEquals('ManyToOne', $card->getDescription());
    }
}
