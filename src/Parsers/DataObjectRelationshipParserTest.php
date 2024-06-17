<?php

namespace PDGA\DataObjects\Parsers;

use PDGA\DataObjects\Models\Test\ModelInstantiatorTestObject;
use PDGA\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class DataObjectRelationshipParserTest extends TestCase
{
    private DataObjectRelationshipParser $relationship_parser;

    public function setUp(): void
    {
        $this->relationship_parser = new DataObjectRelationshipParser();
    }

    public function testEmptyRelationshipReturnsEmptyArray()
    {
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            [],
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals([], $valid_relationships);
    }

    public function testValidRelationshipIsReturned()
    {
        $name = 'FakeHasOneRelation';
        $relationships = [$name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($name, $valid_relationships[0]);
    }

    public function testValidNestedRelationshipIsReturned()
    {
        $name = 'FakeHasOneRelation.NullableFakeHasOneRelation';
        $relationships = [$name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($name, $valid_relationships[0]);
    }

    public function testValidNestedRelationshipWithSpacesIsReturned()
    {
        $expected = 'FakeHasOneRelation.NullableFakeHasOneRelation';
        $name = ' FakeHasOneRelation . NullableFakeHasOneRelation ';
        $relationships = [$name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($expected, $valid_relationships[0]);
    }

    public function testMultipleValidRelationshipsAreReturned()
    {
        $relationships = ['FakeHasOneRelation', 'NullableFakeHasOneRelation', 'FakeHasManyRelation'];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(3, count($valid_relationships));
        $this->assertEquals($relationships, $valid_relationships);
    }

    public function testValidRelationshipWithLeadingWhitespaceIsReturned()
    {
        $name = ' FakeHasOneRelation';
        $relationships = [$name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals(trim($name), $valid_relationships[0]);
    }

    public function testValidRelationshipWithTrailingWhitespaceIsReturned()
    {
        $name = 'FakeHasOneRelation ';
        $relationships = [$name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals(trim($name), $valid_relationships[0]);
    }

    public function testDuplicateValidRelationshipsIgnoresDuplicate()
    {
        $name = 'FakeHasOneRelation';
        $relationships = [$name, $name];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($name, $valid_relationships[0]);
    }

    public function testValidRelationshipWithIncorrectCasingIsReturnedAsCorrectCasing()
    {
        $expected_correct_casing = "FakeHasOneRelation";
        $relationships = ['fAKEhASoNErELATION'];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($expected_correct_casing, $valid_relationships[0]);
    }

    public function testDuplicateValidRelationshipsWithDifferentCasingReturnsSingleRelationship()
    {
        $expected_correct_casing = "FakeHasOneRelation";
        $relationships = [
            $expected_correct_casing,
            strtolower($expected_correct_casing),
            strtoupper($expected_correct_casing)
        ];
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($expected_correct_casing, $valid_relationships[0]);
    }

    public function testInvalidRelationshipThrowsException()
    {
        $name = 'invalid';
        $relationships = [$name];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$name}", $exception->getMessage());
        }
    }

    public function testInvalidNestedRelationshipThrowsException()
    {
        $name = "FakeHasOneRelation.invalid";
        $relationships = [$name];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$name}", $exception->getMessage());
        }
    }

    public function testCircularNestedRelationshipThrowsException()
    {
        $name = "FakeHasOneRelation.NullableFakeHasOneRelation.FakeHasOneRelation";
        $relationships = [$name];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$name}", $exception->getMessage());
        }
    }

    public function testCircularNestedRelationshipIsCaseInsensitiveAndThrowsException()
    {
        $name = "FakeHasOneRelation.NullableFakeHasOneRelation.fakehasonerelation";
        $relationships = [$name];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$name}", $exception->getMessage());
        }
    }

    public function testMultipleInvalidRelationshipsAreIncludedInExceptionMessage()
    {
        $name_1 = 'Invalid1';
        $name_2 = 'invalid2';
        $relationships = [$name_1, $name_2];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$name_1},{$name_2}", $exception->getMessage());
        }
    }

    public function testOnlyInvalidRelationshipsAreIncludedInExceptionMessage()
    {
        $invalid = ' invalid';
        $relationships = ['FakeHasOneRelation', $invalid];

        try {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        } catch (ValidationException $exception) {
            $this->assertEquals("Invalid relationships - {$invalid}", $exception->getMessage());
        }
    }
}
