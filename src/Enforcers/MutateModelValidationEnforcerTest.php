<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\MutateModelValidationEnforcer;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;
use PDGA\Exception\ValidationListException;

class MutatedPerson
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
        isPrimary: true,
    )]
    public int $id;

    #[Column(
        name: 'name',
    )]
    public string $name;
}

class MutateModelValidationEnforcerTest extends TestCase
{
    private $enforcer;

    protected function setUp(): void
    {
        $this->enforcer = new MutateModelValidationEnforcer();
    }

    public function testValidatesInstancesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'name' => 'Joe', 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, MutatedPerson::class);
            $this->assertTrue(true);
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate types. " . json_encode($e->getErrors()));
        }
    }

    public function testNullValueThrowsAnError(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null, 'name' => 'Joe'];
        try
        {
            $this->enforcer->enforce($person, MutatedPerson::class);
            $this->assertTrue(false, "Failed to validate generated fields correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError = "Primary key columns are required and should not be null.";
            $result = $e->getErrors();
            $result = $result['id'][1]['message'];
            $this->assertEquals($expectedError, $result);
        }
    }

    public function testMissingValueThrowsAnError(): void
    {
        $person = ['email' => 'foo@bar.com', 'name' => 'Joe'];
        try
        {
            $this->enforcer->enforce($person, MutatedPerson::class);
            $this->assertTrue(false, "Failed to validate generated fields correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError = "Primary key columns are required and should not be null.";
            $result = $e->getErrors();
            $result = $result['id'][0]['message'];
            $this->assertEquals($expectedError, $result);
        }
    }
}
