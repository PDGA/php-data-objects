<?php

use PHPUnit\Framework\TestCase;

use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\DataObjects\Validators\EmailValidator;
use PDGA\DataObjects\Validators\MaxLengthValidator;
use PDGA\Exception\ValidationListException;

class Person
{
    #[MaxLengthValidator(15), EmailValidator]
    public ?string $email;
    public int $id;
    public ?string $name;
}

class ValidationEnforcerTest extends TestCase
{
    private $enforcer;

    protected function setUp(): void
    {
        $this->enforcer = new ValidationEnforcer();
    }

    public function testMetadataReturnsCorrectNumberOfValidators(): void
    {
        $result = $this->enforcer->getValidationMetadata(Person::class);

        //id validators include int and not null
        $this->assertEquals(count($result['id']['validators']), 2);
        //email validators include string, not blank, max length, and email
        $this->assertEquals(count($result['email']['validators']), 4);
    }

    public function testValidatesInstancesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(true);
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate types. " . json_encode($e->getErrors()));
        }
    }

    public function testTypeValidatesCorrectly(): void
    {
        $person = ['email' => 1234, 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(false, "Failed to validate types correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError = "email must be a string.";
            $result = $e->getErrors();
            $result = $result['email'][0]['message'];
            $this->assertEquals($expectedError, $result);
        }
    }

    public function testMissingParametersAreSkipped(): void
    {
        $person = ['email' => 'foo@bar.com'];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(true);
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate types. " . json_encode($e->getErrors()));
        }
    }

    public function testAttributesValidateCorrectly(): void
    {
        $person = ['email' => 'test a bad string', 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(false, "Failed to validate attributes correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError1 = "Maximum length of email is 15 characters.";
            $expectedError2 = "email must be an email address.";
            $result = $e->getErrors();
            $result1 = $result['email'][0]['message'];
            $result2 = $result['email'][1]['message'];
            $this->assertEquals($expectedError1, $result1);
            $this->assertEquals($expectedError2, $result2);
        }
    }

    public function testAllowedNullValuesAreValidatedCorrectly(): void
    {
        $person = ['email' => null, 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(true);
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate types. " . json_encode($e->getErrors()));
        }
    }

    public function testNotAllowedNullsValidateCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(false, "Failed to validate attributes correctly.");
        }
        catch (ValidationListException $e)
        {
            $expectedError1 = "The id field must not be null.";
            $result = $e->getErrors();
            $result1 = $result['id'][0]['message'];
            $this->assertEquals($expectedError1, $result1);
        }
    }

    public function testPropIsDefinedValidatesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertTrue($this->enforcer->propIsDefined($person, 'id'));
    }

    public function testPropIsDefinedRejectsCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertFalse($this->enforcer->propIsDefined($person, 'test'));
    }

    public function testPropIsNullValidatesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertTrue($this->enforcer->propIsNull($person, 'id'));
    }

    public function testPropIsNullRejectsCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertFalse($this->enforcer->propIsNull($person, 'email'));
    }

    public function testPropIsUndefinedValidatesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertTrue($this->enforcer->propIsUndefined($person, 'test'));
    }

    public function testPropIsUndefinedRejectsCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertFalse($this->enforcer->propIsUndefined($person, 'id'));
    }

    public function testPropIsNotNullValidatesCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertTrue($this->enforcer->propIsNotNull($person, 'email'));
    }

    public function testPropIsNotNullRejectsCorrectly(): void
    {
        $person = ['email' => 'foo@bar.com', 'id' => null];
        $this->assertFalse($this->enforcer->propIsNotNull($person, 'id'));
    }

    public function testNoBlankStrings(): void
    {
        $person = ['name' => '', 'id' => 42];
        try
        {
            $this->enforcer->enforce($person, Person::class);
            $this->assertTrue(false, 'Blank string validation failed.');
        }
        catch (ValidationListException $e)
        {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals('The name field must not be blank.', $e->getErrors()['name'][0]['message']);
        }
    }
}
