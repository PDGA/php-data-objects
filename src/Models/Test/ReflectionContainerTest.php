<?php

namespace PDGA\DataObjects\Models\Test;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use PDGA\DataObjects\Models\ReflectionContainer;
use PDGA\DataObjects\Models\Test\Address;
use PDGA\DataObjects\Models\Test\Member;
use PDGA\DataObjects\Models\Test\Members;
use PDGA\DataObjects\Models\Test\ModelInstantiatorTestObject;
use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Attributes\OneToMany;

class ReflectionContainerTest extends TestCase
{
    private ReflectionContainer $reflection_container;

    public function setUp(): void
    {
        $this->reflection_container = new ReflectionContainer();
    }

    public function testDataObjectPropertyColumns()
    {
        $property_reflection = $this->reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class);
        // We should get an array with property names as keys and the corresponding Column attributes as values.
        $this->assertSame(
            [
                'pdgaNumber',
                'firstName',
                'lastName',
                'email',
                'privacy',
                'birthDate',
            ],
            array_keys($this->reflection_container->dataObjectPropertyColumns($property_reflection))
        );
    }

    public function testDataObjectProperties()
    {
        // We should get an array with all property names as values.
        $this->assertEquals(
            (new ReflectionClass(ModelInstantiatorTestObject::class))->getProperties(),
            $this->reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class)
        );
    }

    public function testDataObjectPropertyCardinalities()
    {
        $member_property_reflection = $this->reflection_container->dataObjectProperties(Member::class);
        $phone_number_property_reflection = $this->reflection_container->dataObjectProperties(PhoneNumber::class);

        $member_cardinalities = $this->reflection_container
            ->dataObjectPropertyCardinalities($member_property_reflection);
        $phone_number_cardinalities = $this->reflection_container
            ->dataObjectPropertyCardinalities($phone_number_property_reflection);

        // This tests that the cardinalities are correct. Deeper testing is difficult as the
        // properties of the cardinality objects are protected.
        $this->assertTrue($member_cardinalities['phoneNumbers'] instanceof OneToMany);
        $this->assertTrue($phone_number_cardinalities['member'] instanceof ManyToOne);
    }

    public function testDataObjectPropertyColumnsReturnsEmptyArray()
    {
        $members_proprety_reflection = $this->reflection_container->dataObjectProperties(Members::class);

        $no_columns = $this->reflection_container->dataObjectPropertyColumns($members_proprety_reflection);
        $no_props   = $this->reflection_container->dataObjectPropertyColumns([]);

        $this->assertTrue($no_columns === []);
        $this->assertTrue($no_props === []);
    }

    public function testDataObjectPropertyCardinalitiesReturnsEmptyArray()
    {
        $address_proprety_reflection = $this->reflection_container->dataObjectProperties(Address::class);

        $no_cardinalities = $this->reflection_container->dataObjectPropertyCardinalities($address_proprety_reflection);
        $no_props         = $this->reflection_container->dataObjectPropertyCardinalities([]);

        $this->assertTrue($no_cardinalities === []);
        $this->assertTrue($no_props === []);
    }
}
