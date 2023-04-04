<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    private string $name;
    private string $sqlDataType;
    private int $maxLength;
    private bool $isPrimary;
    private bool $isGenerated;
    private bool $hasDefault;

    /**
     * Constructor for Column attribute.
     *
     * @param string $name - The name of the column. Required.
     * @param string $sqlDataType - The datatype of the column in the database. Defaults to null.
     * @param int $maxLength - The maximum length of the column. Defaults to null.
     * @param bool $isPrimary - Boolean to indicate if the column is a primary column. Defaults to false.
     * @param bool $isGenerated - Boolean to indicate if the column is auto-generated. Defaults to false.
     * @param bool $hasDefault - Boolean to indicate if the column has a default value. Defaults to false.
     */
    public function __construct(
        string $name,
        string $sqlDataType = null,
        int $maxLength = null,
        bool $isPrimary = false,
        bool $isGenerated = false,
        bool $hasDefault = false
    )
    {
        $this->name        = $name;
        $this->sqlDataType = $sqlDataType;
        $this->maxLength   = $maxLength;
        $this->isPrimary   = $isPrimary;
        $this->isGenerated = $isGenerated;
        $this->hasDefault  = $hasDefault;
    }

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
        return $this->name;
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
}
