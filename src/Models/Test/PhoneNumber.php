<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Models\Test\Member;

class PhoneNumber
{
    public int $pdgaNumber;
    public string $phone;

    #[ManyToOne(Member::class, 'Member')]
    public Member $member;
}
