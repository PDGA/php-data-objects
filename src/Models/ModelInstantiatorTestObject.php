<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\Table;
use PDGA\DataObjects\Converters\YesNoConverter;

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
        converter: YesNoConverter::class,
    )]
    public ?bool $privacy;

    /**
     * Used to test properties without a Column attribute.
     *
     * @var bool
     */
    public bool $testProperty = false;
}
