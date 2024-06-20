<?php

namespace PDGA\DataObjects\Attributes\Test;

use PDGA\DataObjects\Attributes\OneToMany;
use PHPUnit\Framework\TestCase;

class OneToManyTest extends TestCase
{
    public function testDescription(): void
    {
        // Class is ignored here.
        $card = new OneToMany(object::class, 'fake');
        $this->assertEquals('OneToMany', $card->getDescription());
    }
}
