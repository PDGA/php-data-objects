<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;
use PDGA\DataObjects\Converters\Converter;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    /**
     * Constructor for Column attribute.
     *
     * @param string $name - The name of the column. Required.
     * @param ?string $sqlDataType - The datatype of the column in the database. Defaults to null.
     * @param ?int $maxLength - The maximum length of the column. Defaults to null.
     * @param bool $isPrimary - Boolean to indicate if the column is a primary column. Defaults to false.
     * @param bool $isGenerated - Boolean to indicate if the column is auto-generated. Defaults to false.
     * @param bool $hasDefault - Boolean to indicate if the column has a default value. Defaults to false.
     * @param ?Converter $converter - Optional instance of a Converter class for value conversion. Defaults to null.
     */
    public function __construct(
        private string $name,
        private ?string $sqlDataType = null,
        private ?int $maxLength = null,
        private bool $isPrimary = false,
        private bool $isGenerated = false,
        private bool $hasDefault = false,
        private ?Converter $converter = null,
    )
    {}

    /**
     * Returns the name of the column.
     *
     * @return string Returns the name of the column.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the sql datatype of the column or null.
     *
     * @return ?string Returns the sql datatype of the column or null if not set.
     */
    public function getSqlDataType(): ?string
    {
        return $this->sqlDataType;
    }

    /**
     * Returns the max length of the column or null.
     *
     * @return ?int Returns the max length of the column or null if not set.
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * Returns true if the column is a primary one.  False otherwise.
     *
     * @return bool Returns true if the column is primary. False otherwise.
     */
    public function getIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Returns true if the column is auto-generated. False otherwise.
     *
     * @return bool Returns true if the column is auto-generated. False otherwise.
     */
    public function getIsGenerated(): bool
    {
        return $this->isGenerated;
    }

    /**
     * Returns true if the column has a default value. False otherwise.
     *
     * @return bool Returns true if the column has a default value. False otherwise.
     */
    public function getHasDefault(): bool
    {
        return $this->hasDefault;
    }

    /**
     * Returns Converter instance.
     *
     * @return ?Converter
     */
    public function getConverter(): ?Converter
    {
        return $this->converter;
    }
}
