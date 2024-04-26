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
            '',
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals([], $valid_relationships);
    }

    public function testNullRelationshipReturnsEmptyArray()
    {
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            null,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals([], $valid_relationships);
    }

    public function testValidRelationshipIsReturned()
    {
        $relationship = "FakeHasOneRelation";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationship,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($relationship, $valid_relationships[0]);
    }

    public function testMultipleValidRelationshipsAreReturned()
    {
        $relationships = "FakeHasOneRelation,NullableFakeHasOneRelation,FakeHasManyRelation";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationships,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(3, count($valid_relationships));
        $this->assertEquals(explode(',', $relationships), $valid_relationships);
    }

    public function testValidRelationshipWithLeadingWhitespaceIsReturned()
    {
        $relationship = " FakeHasOneRelation";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationship,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals(trim($relationship), $valid_relationships[0]);
    }

    public function testValidRelationshipWithTrailingWhitespaceIsReturned()
    {
        $relationship = "FakeHasOneRelation ";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationship,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals(trim($relationship), $valid_relationships[0]);
    }

    public function testDuplicateValidRelationshipsIgnoresDuplicate()
    {
        $valid = "FakeHasOneRelation";
        $relationship = "{$valid},{$valid}";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationship,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($valid, $valid_relationships[0]);
    }

    public function testValidRelationshipWithIncorrectCasingIsReturnedAsCorrectCasing()
    {
        $expected_correct_casing = "FakeHasOneRelation";
        $relationship = "fAKEhASoNErELATION";
        $valid_relationships = $this->relationship_parser->parseRelationshipsForDataObject(
            $relationship,
            ModelInstantiatorTestObject::class
        );

        $this->assertEquals(1, count($valid_relationships));
        $this->assertEquals($expected_correct_casing, $valid_relationships[0]);
    }

    public function testInvalidRelationshipThrowsException()
    {
        $relationship = "invalid";

        try
        {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationship,
                ModelInstantiatorTestObject::class
            );
            $this->assertTrue(false, "Expected exception not thrown.");
        }
        catch(ValidationException $exception)
        {
            $this->assertEquals("Unknown relationships - {$relationship}", $exception->getMessage());
        }
    }

    public function testMultipleInvalidRelationshipsAreIncludedInExceptionMessage()
    {
        $relationship = "invalid1, invalid2";

        try
        {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationship,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        }
        catch(ValidationException $exception)
        {
            $this->assertEquals("Unknown relationships - {$relationship}", $exception->getMessage());
        }
    }

    public function testOnlyInvalidRelationshipsAreIncludedInExceptionMessage()
    {
        $invalid = " invalid";
        $relationships = "FakeHasOneRelation,{$invalid}";

        try
        {
            $this->relationship_parser->parseRelationshipsForDataObject(
                $relationships,
                ModelInstantiatorTestObject::class
            );

            $this->assertTrue(false, "Expected exception not thrown.");
        }
        catch(ValidationException $exception)
        {
            $this->assertEquals("Unknown relationships - {$invalid}", $exception->getMessage());
        }
    }
}
