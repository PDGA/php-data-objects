<?php

namespace PDGA\DataObjects\DataObjects;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\Table;

/**
 * Placeholder class.
 */
#[Table]
class PdgaMember
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
}
