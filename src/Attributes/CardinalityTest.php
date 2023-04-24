<?php

namespace PDGA\DataObjects\Attributes;

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\OneToMany;

// Test classes.  A Person has Phone Numbers (OTM cardinality).
class Person {}
class PhoneNumber {}

class CardinalityTest extends TestCase
{
    public function testLeftInstance(): void
    {
        $card = new OneToMany(Person::class, PhoneNumber::class);

        $this->assertTrue($card->getLeftInstance() instanceof Person);
    }

    public function testRightInstance(): void
    {
        $card = new OneToMany(Person::class, PhoneNumber::class);

        $this->assertTrue($card->getRightInstance() instanceof PhoneNumber);
    }
}
