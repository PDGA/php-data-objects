<?php

namespace PDGA\DataObjects\Converters;

use PHPUnit\Framework\TestCase;

class JsonConverterTest extends TestCase
{
    private JsonConverter $converter;

    public function setUp(): void
    {
        $this->converter = new JsonConverter();
    }

    public function testOnRetrieve()
    {
        $jsonArray  = $this->getTestDataAsArray();
        $jsonString = $this->getTestDataAsString();

        $this->assertSame(
            $jsonArray,
            $this->converter->onRetrieve($jsonString),
        );
    }

    public function testOnSave()
    {
        $jsonArray  = $this->getTestDataAsArray();
        $jsonString = $this->getTestDataAsString();

        $this->assertSame(
            $jsonString,
            $this->converter->onSave($jsonArray)
        );
    }

    private function getTestDataAsArray()
    {
        return json_decode($this->getTestDataAsString(), true);
    }

    private function getTestDataAsString()
    {
        return '{"testBool":true,"testString":"string","testInt":111,"testFloat":111.111,"testArray":{"foo":"bar"}}';
    }
}
