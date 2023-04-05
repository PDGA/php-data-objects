<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\DataObjects\PdgaMember;
use PHPUnit\Framework\TestCase;

class ModelInstantiatorTest extends TestCase
{
    private ModelInstantiator $model_instantiator;

    public function setUp(): void
    {
        $this->model_instantiator = new ModelInstantiator();
    }

    public function testArrayToDataObject(): void
    {
        // Create an input associative array.
        $array = [
            'pdgaNumber' => 24472,
            'firstName'  => 'Peter',
            'email'      => 'pcrist@pdga.com',
            'fakeProp'   => 'fake'
        ];

        // Convert the array to a data object.
        $data_object = $this->model_instantiator->arrayToDataObject(
            $array,
            PdgaMember::class
        );

        // We should get the correct class instance.
        $this->assertTrue($data_object instanceof PdgaMember);

        // Valid properties should be set.
        $this->assertEquals($array['firstName'], $data_object->firstName);
        $this->assertEquals($array['pdgaNumber'], $data_object->pdgaNumber);
        $this->assertEquals($array['email'], $data_object->email);

        // The extraneous array key should not exist on the data object.
        $this->assertFalse(property_exists($data_object, 'fakeProp'));
    }

    public function testDataObjectToDatabaseModel(): void
    {
        // Create an input data object.
        $data_object             = new PdgaMember();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';

        // We should get a valid database model associative array on conversion.
        $this->assertSame(
            [
                'PDGANum'   => 4297,
                'FirstName' => 'Ken',
                'LastName'  => 'Climo',
                'Email'     => 'champ@pdga.com'
            ],
            $this->model_instantiator->dataObjectToDatabaseModel($data_object)
        );
    }

    public function testDatabaseModelToDataObject(): void
    {
        // Create an input database model associative array.
        $db_model = [
            'PDGANum'   => 4297,
            'FirstName' => 'Ken',
            'LastName'  => 'Climo',
            'Email'     => 'champ@pdga.com'
        ];

        // This data object should match the conversion output.
        $data_object             = new PdgaMember();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';

        $this->assertEquals(
            $data_object,
            $this->model_instantiator->databaseModelToDataObject($db_model, PdgaMember::class)
        );
    }

    public function testDataObjectToArray(): void
    {
        // Create an input data object instance.
        $data_object             = new PdgaMember();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';

        // The output array should reflect the data object properties.
        // Note that the order of the array keys matters and must match the class definition.
        $this->assertSame(
            [
                'pdgaNumber' => 4297,
                'firstName'  => 'Ken',
                'lastName'   => 'Climo',
                'email'      => 'champ@pdga.com'
            ],
            $this->model_instantiator->dataObjectToArray($data_object)
        );
    }
}
