<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\OneToMany;
use PDGA\DataObjects\Models\Test\PhoneNumber;

class Member
{
    #[Column('PDGANumber')]
    public int $pdgaNumber;
    #[Column('FirstName')]
    public string $firstName;
    #[Column('LastName')]
    public string $lastName;

    #[OneToMany(PhoneNumber::class, 'PhoneNumbers')]
    public array $phoneNumbers;
}
