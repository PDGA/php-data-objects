<?php

namespace PDGA\DataObjects\Models;

use PDGA\Exception\ValidationListException;
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
            'pdgaNumber'   => 24472,
            'firstName'    => 'Peter',

            // testProperty exists in the class but does not have a Column attribute.
            'testProperty' => true,

            // fakeProperty does not exist in the class.
            'fakeProperty' => 'faker',
        ];

        try
        {
            // Convert the array to a Data Object.
            $data_object = $this->model_instantiator->arrayToDataObject(
                $array,
                ModelInstantiatorTestObject::class
            );
        }
        catch (ValidationListException $e)
        {
            $this->assertTrue(false, "Failed to validate parameters. " . json_encode($e->getErrors()));
        }

        // We should get the correct class instance.
        $this->assertTrue($data_object instanceof ModelInstantiatorTestObject);

        // Valid properties should be set.
        $this->assertEquals($array['firstName'], $data_object->firstName);
        $this->assertEquals($array['pdgaNumber'], $data_object->pdgaNumber);

        // Unset properties in the array should also be unset in the Data Object instance.
        $this->assertFalse(isset($data_object->email));

        // The property without a Column attribute should be set to the array value.
        $this->assertTrue(property_exists($data_object, 'testProperty'));
        $this->assertEquals($array['testProperty'], $data_object->testProperty);

        // The extraneous array key should not exist as a property or be set on the Data Object.
        $this->assertFalse(property_exists($data_object, 'fakeProperty'));
        $this->assertFalse(isset($data_object->fakeProperty));
    }

    public function testDataObjectToDatabaseModel(): void
    {
        // Create an input Data Object with all Column properties set.
        $data_object             = new ModelInstantiatorTestObject();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';
        $data_object->privacy    = true;

        // We should get a valid database model associative array on conversion.
        $this->assertSame(
            [
                'PDGANum'   => 4297,
                'FirstName' => 'Ken',
                'LastName'  => 'Climo',
                'Email'     => 'champ@pdga.com',
                'Privacy'   => 'yes',
            ],
            $this->model_instantiator->dataObjectToDatabaseModel($data_object)
        );

        // Create an input Data Object with the 'email' property unset.
        $data_object             = new ModelInstantiatorTestObject();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;

        // We should get a valid database model associative array on conversion.
        // Note that 'Email' is not set in the returned array.
        $this->assertSame(
            [
                'PDGANum'   => 4297,
                'FirstName' => 'Ken',
                'LastName'  => 'Climo',
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
            'Email'     => 'champ@pdga.com',
            'Privacy'   => 'yes'
        ];

        // This Data Object should match the conversion output.
        $data_object             = new ModelInstantiatorTestObject();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';
        $data_object->privacy    = true;

        $this->assertEquals(
            $data_object,
            $this->model_instantiator->databaseModelToDataObject($db_model, ModelInstantiatorTestObject::class)
        );
    }

    public function testDataObjectToArray(): void
    {
        // Create an input Data Object instance.
        $data_object               = new ModelInstantiatorTestObject();
        $data_object->firstName    = 'Ken';
        $data_object->lastName     = 'Climo';
        $data_object->pdgaNumber   = 4297;
        $data_object->email        = 'champ@pdga.com';
        $data_object->privacy      = true;
        $data_object->testProperty = true;

        // The output array should reflect the Data Object properties.
        // Note that the order of the array keys matters and must match the class definition.
        $this->assertSame(
            [
                'pdgaNumber'   => 4297,
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'email'        => 'champ@pdga.com',
                'privacy'      => true,
                'testProperty' => true,
            ],
            $this->model_instantiator->dataObjectToArray($data_object)
        );
    }

    public function testDataObjectPropertyColumns()
    {
        // We should get an array with property names as keys and the corresponding Column attributes as values.
        $this->assertSame(
            [
                'pdgaNumber',
                'firstName',
                'lastName',
                'email',
                'privacy',
            ],
            array_keys($this->model_instantiator->dataObjectPropertyColumns(ModelInstantiatorTestObject::class))
        );
    }

    public function testDataObjectProperties()
    {
        // We should get an array with all property names as values.
        $this->assertSame(
            [
                'pdgaNumber',
                'firstName',
                'lastName',
                'email',
                'privacy',
                'testProperty',
            ],
            $this->model_instantiator->dataObjectProperties(ModelInstantiatorTestObject::class)
        );
    }

    public function testConvertPropertyOnSave()
    {
        // Create a Data Object with a property that uses the YesNoConverter.
        $data_object = new ModelInstantiatorTestObject();
        $data_object->privacy = true;

        $columns = $this->model_instantiator->dataObjectPropertyColumns(ModelInstantiatorTestObject::class);

        // Boolean true should return 'yes' when converting to a db model.
        $this->assertSame(
            'yes',
            $this->model_instantiator->convertPropertyOnSave(
                $columns['privacy'],
                $data_object->privacy,
            )
        );
    }

    public function testConvertPropertyOnRetrieve()
    {
        // Create a db model with a property that uses the YesNoConverter.
        $db_model = [
            'Privacy' => 'no',
        ];

        $columns = $this->model_instantiator->dataObjectPropertyColumns(ModelInstantiatorTestObject::class);

        // 'No' should return boolean false when converting to a Data Object.
        $this->assertSame(
            false,
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['privacy'],
                $db_model['Privacy'],
            )
        );
    }
}
