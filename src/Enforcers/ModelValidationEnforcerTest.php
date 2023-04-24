<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ModelValidationEnforcer;
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

class ModelValidationEnforcerTest extends TestCase
{
    private $enforcer;

    protected function setUp(): void
    {
        $this->enforcer = new ModelValidationEnforcer();
    }

    public function testMetadataReturnsCorrectNumberOfValidators(): void
    {
        $result = $this->enforcer->getValidationMetadata(NamedPerson::class);

        //id validators include int and not null
        $this->assertEquals(count($result['id']['validators']), 2);
        //email validators include string, not blank, max length, max length
        //(from the column) and email
        $this->assertEquals(count($result['email']['validators']), 5);
        //name validators include string, not blank, max length, max length
        //(from the column), and not null
        $this->assertEquals(count($result['name']['validators']), 5);
    }

}
