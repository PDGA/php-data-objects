<?php

namespace PDGA\DataObjects\Models\Test;

use \DateTime;

use PDGA\DataObjects\Models\Test\ModelInstantiatorTestObject;
use PDGA\DataObjects\Models\Test\PrivacyProtectedTestDataObject;
use PHPUnit\Framework\TestCase;

use PDGA\Exception\InvalidRelationshipDataException;
use PDGA\Exception\ValidationException;
use PDGA\Exception\ValidationListException;

use PDGA\DataObjects\Models\ModelInstantiator;
use PDGA\DataObjects\Models\ReflectionContainer;
use PDGA\DataObjects\Models\Test\Member;
use PDGA\DataObjects\Models\Test\ModelInstantiatorTestDBModel;
use PDGA\DataObjects\Models\Test\PhoneNumber;

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
            'birthDate'    => '2020-01-01',

            // testProperty exists in the class but does not have a Column attribute.
            'testProperty' => true,

            // fakeProperty does not exist in the class.
            'fakeProperty' => 'faker',
        ];

        try {
            // Convert the array to a Data Object.
            $data_object = $this->model_instantiator->arrayToDataObject(
                $array,
                ModelInstantiatorTestObject::class
            );
        } catch (ValidationListException $e) {
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

    /**
     * Tests instantiation of a nested array of Data Objects.
     */
    public function testArrayToDataObjectNestedArray(): void
    {
        $member_arr = [
            'pdgaNumber' => 42,
            'firstName'  => 'Jeff',
            'lastName'   => 'Lebowski',

            // This should get assigned to an array of PhoneNumber instances.
            'phoneNumbers' => [
                ['pdgaNumber' => 42, 'phone' => '999-888-7777'],
                ['pdgaNumber' => 42, 'phone' => '111-222-3333'],
            ],
        ];

        $member = $this->model_instantiator->arrayToDataObject(
            $member_arr,
            Member::class
        );

        $this->assertEquals(2, count($member->phoneNumbers));
        $this->assertTrue($member->phoneNumbers[0] instanceof PhoneNumber);
        $this->assertEquals(
            $member_arr['phoneNumbers'][0]['pdgaNumber'],
            $member->phoneNumbers[0]->pdgaNumber,
        );

        $this->assertTrue($member->phoneNumbers[1] instanceof PhoneNumber);
        $this->assertEquals(
            $member_arr['phoneNumbers'][1]['phone'],
            $member->phoneNumbers[1]->phone,
        );

        $this->assertTrue(true);
    }

    /**
     * Tests instantiation of a single nested Data Objects.
     */
    public function testArrayToDataObjectNestedObject(): void
    {
        $pn_arr = [
            'pdgaNumber' => 42,
            'phone'      => '123-456-7890',

            // Maps to a single Member instance.
            'member' => [
                'pdgaNumber' => 42,
                'firstName'  => 'Jeff',
                'lastName'   => 'Lebowski',
            ],
        ];

        $pn = $this->model_instantiator->arrayToDataObject(
            $pn_arr,
            PhoneNumber::class,
        );

        $this->assertTrue($pn->member instanceof Member);
        $this->assertEquals($pn->member->firstName, 'Jeff');
    }

    /**
     * Raises a validation exception when a nested object is not an array.
     */
    public function testArrayToDataObjectBadNestedObject(): void
    {
        $pn_arr = [
            'phone' => '123-456-7890',
            'member' => 'I am not an array.',
        ];

        try {
            $this->model_instantiator->arrayToDataObject(
                $pn_arr,
                PhoneNumber::class,
            );
            $this->assertTrue(false);
        } catch (ValidationException $e) {
            $this->assertEquals('member must be an associative array.', $e->getMessage());
        }
    }

    public function testArrayToDataObjectFailsWithEmptyString(): void
    {
        // Create an input associative array.
        $array = [
            'displayName' => '',
        ];

        try {
            // Convert the array to a Data Object.
            $data_object = $this->model_instantiator->arrayToDataObject(
                $array,
                ModelInstantiatorTestObject::class
            );

            $this->fail("Expected exception not thrown.");
        } catch (ValidationListException $e) {
            $expectedError = "The displayName field must not be blank.";
            $result = $e->getErrors()['displayName'][0]['message'];
            $this->assertEquals($expectedError, $result);
        }
    }

    public function testDataObjectToDatabaseModel(): void
    {
        // Create an input Data Object with all Column properties set.
        $data_object              = new ModelInstantiatorTestObject();
        $data_object->firstName   = 'Ken';
        $data_object->lastName    = 'Climo';
        $data_object->displayName = ''; // This will get converted to null
        $data_object->pdgaNumber  = 4297;
        $data_object->email       = 'champ@pdga.com';
        $data_object->privacy     = true; // This will get converted to 'yes'
        $data_object->birthDate   = new DateTime('2020-01-01');

        // We should get a valid database model associative array on conversion.
        $this->assertSame(
            [
                'PDGANum'   => 4297,
                'FirstName' => 'Ken',
                'LastName'  => 'Climo',
                'DisplayName' => null,
                'Email'     => 'champ@pdga.com',
                'Privacy'   => 'yes',
                'BirthDate' => '2020-01-01T00:00:00+00:00',
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

    public function testDataObjectToDatabaseModelConvertsNulls(): void
    {
        $data_object             = new ModelInstantiatorTestObject();
        $data_object->firstName  = null;

        // We should get a valid database model associative array on conversion.
        $this->assertSame(
            [
                'FirstName' => null,
            ],
            $this->model_instantiator->dataObjectToDatabaseModel($data_object)
        );
    }

    public function testDatabaseModelToDataObjectWithAttributesGetter(): void
    {
        // This fake DB model mimics an Eloquent model in that it has a private
        // array of attributes, only accessible through a getter.  This test
        // shows that when the supplied DB model has a getAttributes method,
        // it's used.
        $pdga_num                 = 123;
        $display_name             = null;
        $db_model                 = new ModelInstantiatorTestDBModel($pdga_num, $display_name);
        $data_object              = new ModelInstantiatorTestObject();
        $data_object->pdgaNumber  = $pdga_num;
        $data_object->displayName = $display_name;

        // Note that PDGANum is private in the DB model, so the only way the
        // ModelInstantiator can access it is via getAttributes.
        $this->assertEquals(
            $data_object,
            $this->model_instantiator->databaseModelToDataObject($db_model, ModelInstantiatorTestObject::class)
        );
    }

    public function testDatabaseModelToDataObjectWithEmptyStringToNullConversion(): void
    {
        // This fake DB model mimics an Eloquent model in that it has a private
        // array of attributes, only accessible through a getter.  This test
        // shows that when the supplied DB model has a getAttributes method,
        // it's used.
        $pdga_num = 123;
        $display_name = '';
        $db_model = new ModelInstantiatorTestDBModel($pdga_num, $display_name);

        $expected_data_object = new ModelInstantiatorTestObject();
        $expected_data_object->pdgaNumber = $pdga_num;
        $expected_data_object->displayName = null;

        $result = $this->model_instantiator->databaseModelToDataObject(
            $db_model,
            ModelInstantiatorTestObject::class
        );

        // Note that PDGANum is private in the DB model, so the only way the
        // ModelInstantiator can access it is via getAttributes.
        $this->assertSame($expected_data_object->pdgaNumber, $result->pdgaNumber);
        $this->assertNull($result->displayName);
        $this->assertSame($expected_data_object->displayName, $result->displayName);
    }

    public function testDatabaseModelToDataObjectNestedArrayWithRelationsGetter(): void
    {
        // A DB model with a fake one-to-many relationship (self-related).  The
        // DB model has a getRelations method, mimicking an Eloquent model, and
        // the ModelInstantiator uses this method to pull relationships.
        $db_model = new ModelInstantiatorTestDBModel(123);
        $relation = new ModelInstantiatorTestDBModel(456);

        $db_model->addManyRelation($relation);

        $data_object = $this->model_instantiator->databaseModelToDataObject(
            $db_model,
            ModelInstantiatorTestObject::class,
        );

        // The ModelInstaniator finds the relationship via a getRelations call,
        // and correctly instantiates the array of
        // ModelInstantiatorTestObjects.
        $this->assertEquals(count($data_object->fakeHasManyRelation), 1);
        $this->assertEquals($data_object->fakeHasManyRelation[0]->pdgaNumber, 456);
    }

    public function testDatabaseModelToDataObjectNestedObjectWithRelationsGetter(): void
    {
        // Same as the previous test, but verifies many-to-one relationships.
        $db_model = new ModelInstantiatorTestDBModel(123);
        $relation = new ModelInstantiatorTestDBModel(456);

        $db_model->addOneRelation($relation);

        $data_object = $this->model_instantiator->databaseModelToDataObject(
            $db_model,
            ModelInstantiatorTestObject::class,
        );

        $this->assertEquals($data_object->fakeHasOneRelation->pdgaNumber, 456);
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
        $data_object->birthDate    = new DateTime('2020-01-01');

        // The output array should reflect the Data Object properties.
        // Note that the order of the array keys matters and must match the class definition.
        $result = $this->model_instantiator->dataObjectToArray($data_object);

        $this->assertSame(
            [
                'pdgaNumber'   => 4297,
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'email'        => 'champ@pdga.com',
                'privacy'      => true,
                'birthDate'    => '2020-01-01T00:00:00+00:00',
                'testProperty' => true,
            ],
            $result
        );
    }

    public function testDataObjectToArrayWithPrivacyProtectedDataObject(): void
    {
        // Create an input Privacy Protected Data Object instance.
        $data_object = $this->getPrivacyProtectedTestDataObject();

        // Assert that the privacy protected properties are set prior to array conversion.
        $this->assertPrivacyProtectedPropertiesAreSet($data_object);

        // The output array should reflect the Data Object properties.
        // The values for the privacy protected fields should be removed.
        $result = $this->model_instantiator->dataObjectToArray($data_object);

        $this->assertSame(
            [
                'pdgaNumber'   => 4297,
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'testProperty' => true,
            ],
            $result
        );

        foreach (PrivacyProtectedTestDataObject::PRIVACY_PROTECTED_PROPERTIES as $property) {
            $this->assertFalse(isset($result[$property]));
        }
    }

    public function testDataObjectToArrayWithPrivacyProtectedDataObjectButAlsoOverridden(): void
    {
        // Create an input Data Object instance.
        $data_object               = new ModelInstantiatorTestObject();
        $data_object->firstName    = 'Ken';
        $data_object->lastName     = 'Climo';
        $data_object->pdgaNumber   = 4297;
        $data_object->email        = 'champ@pdga.com';
        $data_object->privacy      = true;
        $data_object->testProperty = true;
        $data_object->birthDate    = new DateTime('2020-01-01');

        // The output array should reflect the Data Object properties.
        // Note that the order of the array keys matters and must match the class definition.
        $cleanse_privacy = false;
        $result = $this->model_instantiator->dataObjectToArray($data_object, $cleanse_privacy);

        $this->assertSame(
            [
                'pdgaNumber'   => 4297,
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'email'        => 'champ@pdga.com',
                'privacy'      => true,
                'birthDate'    => '2020-01-01T00:00:00+00:00',
                'testProperty' => true,
            ],
            $result
        );
    }

    public function testNestedDataObjectToArrayWithPrivacyProtectedDataObject(): void
    {
        // Create a related Privacy Protected Data Object instance.
        $fake_has_one_data_object = $this->getPrivacyProtectedTestDataObject();
        $nullable_fake_has_one_data_object = $this->getPrivacyProtectedTestDataObject();
        $fake_has_many_data_object = [$this->getPrivacyProtectedTestDataObject()];

        // Create the primary data object and set relationship values.
        $data_object = $this->getPrivacyProtectedTestDataObject();
        $data_object->fakeHasOneRelation = $fake_has_one_data_object;
        $data_object->nullableFakeHasOneRelation = $nullable_fake_has_one_data_object;
        $data_object->fakeHasManyRelation = $fake_has_many_data_object;

        // Assert that the privacy protected properties are set prior to array conversion.
        $this->assertPrivacyProtectedPropertiesAreSet($data_object);
        $this->assertPrivacyProtectedPropertiesAreSet($fake_has_one_data_object);
        $this->assertPrivacyProtectedPropertiesAreSet($nullable_fake_has_one_data_object);
        $this->assertPrivacyProtectedPropertiesAreSet($fake_has_many_data_object[0]);

        // The output array should reflect the Data Object properties.
        // The values for the privacy protected fields should be removed.
        $result = $this->model_instantiator->dataObjectToArray($data_object);

        $this->assertSame(
            [
                'pdgaNumber'   => 4297,
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'testProperty' => true,
                'fakeHasOneRelation' => [
                    'pdgaNumber'   => 4297,
                    'firstName'    => 'Ken',
                    'lastName'     => 'Climo',
                    'testProperty' => true,
                ],
                'nullableFakeHasOneRelation' => [
                    'pdgaNumber'   => 4297,
                    'firstName'    => 'Ken',
                    'lastName'     => 'Climo',
                    'testProperty' => true,
                ],
                'fakeHasManyRelation' => [[
                    'pdgaNumber'   => 4297,
                    'firstName'    => 'Ken',
                    'lastName'     => 'Climo',
                    'testProperty' => true,
                ]],
            ],
            $result
        );
    }

    public function testPartialDataObjectToArray(): void
    {
        $data_object               = new ModelInstantiatorTestObject();
        $data_object->firstName    = 'Ken';
        $data_object->lastName     = 'Climo';

        $this->assertSame(
            [
                'firstName'    => 'Ken',
                'lastName'     => 'Climo',
                'testProperty' => false, // Default.
            ],
            $this->model_instantiator->dataObjectToArray($data_object)
        );
    }

    public function testDataObjectToArrayNested(): void
    {
        // This is a goofy structure, but it tests recursion of both nested
        // objects and arrays.  A Member with one Phone Number with a Member.
        $member = new Member();
        $member->pdgaNumber = 42;
        $member->firstName  = 'Jane';

        $member->phoneNumbers = [new PhoneNumber()];
        $member->phoneNumbers[0]->pdgaNumber = 42;
        $member->phoneNumbers[0]->phone      = '123-456-7890';

        $member->phoneNumbers[0]->member = new Member();
        $member->phoneNumbers[0]->member->pdgaNumber = 42;
        $member->phoneNumbers[0]->member->firstName  = 'Jane';

        $this->assertEqualsCanonicalizing(
            [
                'firstName'    => 'Jane',
                'pdgaNumber'   => 42,
                'phoneNumbers' => [
                    [
                        'pdgaNumber' => 42,
                        'phone' => '123-456-7890',
                        'member' => [
                            'firstName'    => 'Jane',
                            'pdgaNumber'   => 42,
                        ],
                    ],
                ],
            ],
            $this->model_instantiator->dataObjectToArray($member),
        );
    }

    public function testDataObjectToArrayDoesNotConvertValues(): void
    {
        // This tests that the property converters are not used when converting a data object to an array.
        $member = new ModelInstantiatorTestObject();
        $member->pdgaNumber = 42;
        $member->displayName  = '';

        $result = $this->model_instantiator->dataObjectToArray($member);

        $this->assertSame($member->pdgaNumber, $result['pdgaNumber']);
        $this->assertSame($member->displayName, $result['displayName']);
    }

    public function testConvertPropertyOnSave()
    {
        $reflection_container = new ReflectionContainer();

        // Create a Data Object with a property that uses the YesNoConverter.
        $data_object = new ModelInstantiatorTestObject();
        $data_object->privacy = true;
        $data_object->displayName = '';
        $property_reflection = $reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class);

        $columns = $reflection_container->dataObjectPropertyColumns($property_reflection);

        // Boolean true should return 'yes' when converting to a db model.
        $this->assertSame(
            'yes',
            $this->model_instantiator->convertPropertyOnSave(
                $columns['privacy'],
                $data_object->privacy,
            )
        );

        // This uses the empty string to null conversion
        $this->assertNull(
            $this->model_instantiator->convertPropertyOnSave(
                $columns['displayName'],
                $data_object->displayName,
            )
        );
    }

    public function testDontConvertNullPropertyOnSave()
    {
        $reflection_container = new ReflectionContainer();

        // Privacy uses the yes/no converter, but it's null so it shouldn't be
        // converted.
        $data_object = new ModelInstantiatorTestObject();
        $data_object->privacy = null;
        $data_object->displayName = null;
        $property_reflection = $reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class);

        $columns = $reflection_container->dataObjectPropertyColumns($property_reflection);

        $this->assertNull(
            $this->model_instantiator->convertPropertyOnSave(
                $columns['privacy'],
                $data_object->privacy,
            )
        );

        $this->assertNull(
            $this->model_instantiator->convertPropertyOnSave(
                $columns['displayName'],
                $data_object->displayName,
            )
        );
    }

    public function testConvertPropertyOnRetrieve()
    {
        $reflection_container = new ReflectionContainer();

        // Create a db model with a property that uses the YesNoConverter.
        $db_model = [
            'Privacy' => 'no',
            'DisplayName' => '',
        ];

        $property_reflection = $reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class);
        $columns = $reflection_container->dataObjectPropertyColumns($property_reflection);

        // 'No' should return boolean false when converting to a Data Object.
        $this->assertSame(
            false,
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['privacy'],
                $db_model['Privacy'],
            )
        );

        // This uses the empty string to null conversion
        $this->assertNull(
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['displayName'],
                $db_model['DisplayName'],
            )
        );
    }

    public function testDontConvertNullPropertyOnRetrieve()
    {
        $reflection_container = new ReflectionContainer();

        $db_model = [
            'Privacy' => null,
            'DisplayName' => '',
        ];

        $property_reflection = $reflection_container->dataObjectProperties(ModelInstantiatorTestObject::class);
        $columns = $reflection_container->dataObjectPropertyColumns($property_reflection);

        $this->assertNull(
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['privacy'],
                $db_model['Privacy'],
            )
        );

        $this->assertNull(
            $this->model_instantiator->convertPropertyOnRetrieve(
                $columns['displayName'],
                $db_model['DisplayName'],
            )
        );
    }

    public function testDatabaseModelToDataObjectNullableNestedObjectNotNullWithRelationsGetter(): void
    {
        // Verify a nullable many to one relationship works when the data is defined.
        $db_model = new ModelInstantiatorTestDBModel(123);
        $relation = new ModelInstantiatorTestDBModel(456);

        $db_model->addNullableOneRelation($relation);

        $data_object = $this->model_instantiator->databaseModelToDataObject(
            $db_model,
            ModelInstantiatorTestObject::class,
        );

        $this->assertEquals($data_object->nullableFakeHasOneRelation->pdgaNumber, 456);
    }

    public function testDatabaseModelToDataObjectNullableNestedObjectNullWithRelationsGetter(): void
    {
        // Verify a nullable many to one relationship works when the data is null.
        $db_model = new ModelInstantiatorTestDBModel(123);
        $relation = null;

        $db_model->addNullableOneRelation($relation);

        $data_object = $this->model_instantiator->databaseModelToDataObject(
            $db_model,
            ModelInstantiatorTestObject::class,
        );

        $this->assertEquals($data_object->nullableFakeHasOneRelation, null);
    }

    public function testDatabaseModelToDataObjectNestedObjectWithRelationsGetterExcepts(): void
    {
        // Verify a many to one relationship errors out with the correct exception type when the data is null.
        $db_model = new ModelInstantiatorTestDBModel(123);
        $relation = null;

        $db_model->addOneRelation($relation);

        try {
            $data_object = $this->model_instantiator->databaseModelToDataObject(
                $db_model,
                ModelInstantiatorTestObject::class,
            );
            $this->assertTrue(false);
        } catch (InvalidRelationshipDataException $e) {
            $this->assertEquals('FakeHasOneRelation relationship must not be null.', $e->getMessage());
        }
    }

    /**
     * @return PrivacyProtectedTestDataObject
     */
    private function getPrivacyProtectedTestDataObject(): PrivacyProtectedTestDataObject
    {
        // Create an input Privacy Protected Data Object instance.
        $data_object = new PrivacyProtectedTestDataObject();
        $data_object->firstName = 'Ken';
        $data_object->lastName = 'Climo';
        $data_object->pdgaNumber = 4297;
        $data_object->email = 'champ@pdga.com';
        $data_object->privacy = true;
        $data_object->testProperty = true;
        $data_object->birthDate = new DateTime('2020-01-01');

        return $data_object;
    }

    private function assertPrivacyProtectedPropertiesAreSet(PrivacyProtectedTestDataObject $data_object): void
    {
        foreach (PrivacyProtectedTestDataObject::PRIVACY_PROTECTED_PROPERTIES as $property) {
            $this->assertTrue(isset($data_object->$property));
        }
    }
}
