<?php

namespace PDGA\DataObjects\Enforcers\Test;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;

class NamedPerson
{
    #[Column(
        name: 'email',
        maxLength: 30
    )]
    #[MaxLengthValidator(15), EmailValidator]
    public ?string $email;

    public int $id;

    #[Column(
        name: 'name',
        maxLength: 20
    )]
    #[MaxLengthValidator(15)]
    public string $name;
}
