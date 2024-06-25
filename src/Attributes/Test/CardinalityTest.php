<?php

namespace PDGA\DataObjects\Attributes\Test;

use PDGA\DataObjects\Attributes\OneToMany;
use PHPUnit\Framework\TestCase;

class CardinalityTest extends TestCase
{
    public function testRelationClass(): void
    {
        $card = new OneToMany(TestPhoneNumber::class, 'PhoneNumbers');

        $this->assertEquals(
            'PDGA\DataObjects\Attributes\Test\TestPhoneNumber',
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
