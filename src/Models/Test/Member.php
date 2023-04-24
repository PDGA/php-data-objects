<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\OneToMany;
use PDGA\DataObjects\Models\Test\PhoneNumber;

class Member
{
    public int $pdgaNumber;
    public string $firstName;
    public string $lastName;

    #[OneToMany(PhoneNumber::class)]
    public array $phoneNumbers;
}
