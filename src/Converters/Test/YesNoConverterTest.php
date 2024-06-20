<?php

namespace PDGA\DataObjects\Converters\Test;

use PDGA\DataObjects\Converters\YesNoConverter;
use PHPUnit\Framework\TestCase;

class YesNoConverterTest extends TestCase
{
    private YesNoConverter $converter;

    public function setUp(): void
    {
        $this->converter = new YesNoConverter();
    }

    public function testOnRetrieve()
    {
        // 'yes' converts to boolean true.
        $this->assertSame(
            true,
            $this->converter->onRetrieve('yes')
        );

        // 'no' converts to boolean false.
        $this->assertSame(
            false,
            $this->converter->onRetrieve('no')
        );

        // null converts to boolean false.
        $this->assertSame(
            false,
            $this->converter->onRetrieve(null)
        );

        // Empty string converts to boolean false.
        $this->assertSame(
            false,
            $this->converter->onRetrieve('')
        );
    }

    public function testOnSave()
    {
        // Boolean true converts to 'yes'.
        $this->assertSame(
            'yes',
            $this->converter->onSave(true)
        );

        // Boolean false converts to 'no'.
        $this->assertSame(
            'no',
            $this->converter->onSave(false)
        );

        // Truthy value (1) converts to 'no'.
        $this->assertSame(
            'no',
            $this->converter->onSave(1)
        );

        // Truthy value ('abc') converts to 'no'.
        $this->assertSame(
            'no',
            $this->converter->onSave('abc')
        );

        // Falsy value (0) converts to 'no'.
        $this->assertSame(
            'no',
            $this->converter->onSave(0)
        );

        // Falsy value ('') converts to 'no'.
        $this->assertSame(
            'no',
            $this->converter->onSave('')
        );
    }
}
