<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Models\Test\Member;

class PhoneNumber
{
    #[Column('PDGANumber')]
    public int $pdgaNumber;
    #[Column('Phone')]
    public string $phone;

    #[ManyToOne(Member::class, 'Member')]
    public Member $member;
}
