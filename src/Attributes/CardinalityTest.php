<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\OneToMany;

class TestPhoneNumber {}

class CardinalityTest extends TestCase
{
    public function testRelationClass(): void
    {
        $card = new OneToMany(TestPhoneNumber::class);

        $this->assertEquals(
            'PDGA\DataObjects\Attributes\TestPhoneNumber',
            $card->getRelationClass(),
        );
    }

    public function testRelationInstance(): void
    {
        $card = new OneToMany(TestPhoneNumber::class);

        $this->assertTrue($card->getRelationInstance() instanceof TestPhoneNumber);
    }
}
