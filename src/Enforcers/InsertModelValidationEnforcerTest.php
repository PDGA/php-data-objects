<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\InsertModelValidationEnforcer;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;
use PDGA\Exception\ValidationListException;

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

class InsertModelValidationEnforcerTest extends TestCase
{
    private $enforcer;

    protected function setUp(): void
    {
        $this->enforcer = new InsertModelValidationEnforcer();
    }

    public function testValidatesInstancesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'name' => 'Joe'];
        try
        {
            $this->enforcer->enforce($person, InsertedPerson::class);
            $this->assertTrue(true);
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate types. " . json_encode($e->getErrors()));
        }
    }

    public function testGeneratedFieldsThrowErrors(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => 42, 'name' => 'Joe'];
        try
        {
            $this->enforcer->enforce($person, InsertedPerson::class);
            $this->assertTrue(false, "Failed to validate generated fields correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError = "id should not be defined for an insert.";
            $result = $e->getErrors();
            $result = $result['id'][0]['message'];
            $this->assertEquals($expectedError, $result);
        }
    }

    public function testNonNullableFieldsWithNoDefaultThrowErrors(): void
    {
        $person = ['email' => 'foo@bar.com'];
        try
        {
            $this->enforcer->enforce($person, InsertedPerson::class);
            $this->assertTrue(false, "Failed to validate non nullable value with no default values correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError = "Non-nullable properties with no default value are required.";
            $result = $e->getErrors();
            $result = $result['name'][0]['message'];
            $this->assertEquals($expectedError, $result);
        }

        $person2 = ['email' => 'foo@bar.com', 'name' => null];
        try
        {
            $this->enforcer->enforce($person2, InsertedPerson::class);
            $this->assertTrue(false, "Failed to validate non nullable value with no default values correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError2 = "Non-nullable properties with no default value are required.";
            $result2 = $e->getErrors();
            $result2 = $result2['name'][1]['message'];
            $this->assertEquals($expectedError2, $result2);
        }
    }
}
