<?php

namespace PDGA\DataObjects\Enforcers\Test;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ModelValidationEnforcer;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;
use PHPUnit\Framework\TestCase;

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
        //email validators include string, max length, max length(from the column), and email
        $this->assertEquals(count($result['email']['validators']), 4);
        //name validators include string, max length, max length(from the column), and not null
        $this->assertEquals(count($result['name']['validators']), 4);
    }
}
