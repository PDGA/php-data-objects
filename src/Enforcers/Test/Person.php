<?php

namespace PDGA\DataObjects\Enforcers\Test;

use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;

class Person
{
    #[MaxLengthValidator(15), EmailValidator]
    public ?string $email;
    public int $id;
    public ?string $name;
    public array $widgets;
}
