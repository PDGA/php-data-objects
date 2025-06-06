<?php

namespace PDGA\DataObjects\Models\Test;

use DateTime;
use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Attributes\OneToMany;
use PDGA\DataObjects\Attributes\Table;
use PDGA\DataObjects\Converters\YesNoConverter;
use PDGA\DataObjects\Converters\DateTimeConverter;

/**
 * Placeholder class.
 */
#[Table]
class ModelInstantiatorTestObject
{
    #[Column(
        name: 'PDGANum',
        sqlDataType: 'int',
    )]
    public ?int $pdgaNumber;

    #[Column(
        name: 'FirstName',
        sqlDataType: 'varchar',
    )]
    public ?string $firstName;

    #[Column(
        name: 'LastName',
        sqlDataType: 'varchar',
    )]
    public ?string $lastName;

    #[Column(
        name: 'Email',
        sqlDataType: 'varchar',
    )]
    public ?string $email;

    #[Column(
        name: 'Privacy',
        sqlDataType: 'enum',
        converter: new YesNoConverter(),
    )]
    public ?bool $privacy;

    #[Column(
        name: 'BirthDate',
        sqlDataType: 'date',
        converter: new DateTimeConverter(),
    )]
    public ?DateTime $birthDate;

    /**
     * Used to test properties without a Column attribute.
     *
     * @var bool
     */
    public bool $testProperty = false;

    // Used to test ManyToOne relations.
    #[ManyToOne(
        ModelInstantiatorTestObject::class,
        'FakeHasOneRelation',
    )]
    public ModelInstantiatorTestObject $fakeHasOneRelation;

    // Used to test nullable ManyToOne relations.
    #[ManyToOne(
        ModelInstantiatorTestObject::class,
        'NullableFakeHasOneRelation',
    )]
    public ?ModelInstantiatorTestObject $nullableFakeHasOneRelation;

    // Used to test OneToMany relations.
    #[OneToMany(
        ModelInstantiatorTestObject::class,
        'FakeHasManyRelation',
    )]
    public array $fakeHasManyRelation;
}
