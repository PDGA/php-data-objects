<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Attributes\ManyToOne;
use PDGA\DataObjects\Models\Test\Member;

class Members
{
    #[ManyToOne(Member::class, 'Member')]
    public array $members;
}
