<?php

namespace PDGA\DataObjects\Models;

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
            'testProperty' => 'test',

            // fakeProperty does not exist in the class.
            'fakeProperty' => 'faker',
        ];

        // Convert the array to a data object.
        $data_object = $this->model_instantiator->arrayToDataObject(
            $array,
            ModelInstantiatorTestObject::class
        );

        // We should get the correct class instance.
        $this->assertTrue($data_object instanceof ModelInstantiatorTestObject);

        // Valid properties should be set.
        $this->assertEquals($array['firstName'], $data_object->firstName);
        $this->assertEquals($array['pdgaNumber'], $data_object->pdgaNumber);

        // Unset properties in the array should also be unset in the data object instance.
        $this->assertFalse(isset($data_object->email));

        // The property without a Column attribute exist on the data object but not be set to the array value.
        $this->assertTrue(property_exists($data_object, 'testProperty'));
        $this->assertNotEquals($array['testProperty'], $data_object->testProperty);

        // The extraneous array key should not exist as a property or be set on the data object.
        $this->assertFalse(property_exists($data_object, 'fakeProperty'));
        $this->assertFalse(isset($data_object->fakeProperty));
    }

    public function testDataObjectToDatabaseModel(): void
    {
        // Create an input data object with all Column properties set.
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

        // Create an input data object with the 'email' property unset.
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

        // This data object should match the conversion output.
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
        // Create an input data object instance.
        $data_object             = new ModelInstantiatorTestObject();
        $data_object->firstName  = 'Ken';
        $data_object->lastName   = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email      = 'champ@pdga.com';
        $data_object->privacy    = true;

        // The output array should reflect the data object properties.
        // Note that the order of the array keys matters and must match the class definition.
        $this->assertSame(
            [
                'pdgaNumber' => 4297,
                'firstName'  => 'Ken',
                'lastName'   => 'Climo',
                'email'      => 'champ@pdga.com',
                'privacy'    => true,
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
                'privacy'
            ],
            array_keys($this->model_instantiator->dataObjectPropertyColumns(ModelInstantiatorTestObject::class))
        );
    }

    public function testConvertPropertyOnSave()
    {
        // Create a data object with a property that uses the YesNoConverter.
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

        // 'No' should return boolean false when converting to a data object.
        $this->assertSame(
            false,
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['privacy'],
                $db_model['Privacy'],
            )
        );
    }
}
