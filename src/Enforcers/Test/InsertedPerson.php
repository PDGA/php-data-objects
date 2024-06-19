<?php

namespace PDGA\DataObjects\Enforcers\Test;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;

class InsertedPerson
{
    #[Column(
        name: 'email',
        maxLength: 30,
    )]
    #[MaxLengthValidator(15), EmailValidator]
    public ?string $email;

    #[Column(
        name: 'id',
        isGenerated: true,
    )]
    public int $id;

    #[Column(
        name: 'name',
    )]
    public string $name;
}
