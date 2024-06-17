<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\OneToMany;

class TestPhoneNumber
{
}

class CardinalityTest extends TestCase
{
    public function testRelationClass(): void
    {
        $card = new OneToMany(TestPhoneNumber::class, 'PhoneNumbers');

        $this->assertEquals(
            'PDGA\DataObjects\Attributes\TestPhoneNumber',
            $card->getRelationClass(),
        );
    }

    public function testRelationInstance(): void
    {
        $card = new OneToMany(TestPhoneNumber::class, 'PhoneNumbers');

        $this->assertTrue($card->getRelationInstance() instanceof TestPhoneNumber);
    }

    public function testGetAlias(): void
    {
        $card = new OneToMany(TestPhoneNumber::class, 'PhoneNumbers');

        $this->assertEquals('PhoneNumbers', $card->getAlias());
    }
}
