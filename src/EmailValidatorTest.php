<?php

namespace PDGA\DataObjects;

use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    private EmailValidator $validator;

    public function setUp(): void
    {
        $this->validator = new EmailValidator();
    }

    public function testGetErrorMessage()
    {
        $propName = 'Email';

        $this->assertEquals(
            "$propName must be an email address.",
            $this->validator->getErrorMessage($propName)
        );
    }

    public function testNullValidates()
    {
        $email = null;

        $this->assertTrue($this->validator->validate($email));
    }

    public function testValidEmailValidates()
    {
        $valid_email_addresses = [
            'technology@pdga.com',
            'technology.test@pdga.com',
            'technology+test@pdga.com',
            'pdga-dev@gmail.com',
            'pdga-dev@gmail.com',
        ];

        foreach ($valid_email_addresses as $email_address)
        {
            $this->assertTrue($this->validator->validate($email_address));
        }
    }

    public function testInvalidEmailFails()
    {
        $invalid_email_addresses = [
            'technology@pdga',
            'technology test@pdga.com',
            'technologypdga.com',
            'pdga-dev@.com',
            '',
        ];

        foreach ($invalid_email_addresses as $email_address)
        {
            $this->assertFalse($this->validator->validate($email_address));
        }
    }
}
