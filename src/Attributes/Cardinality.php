<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

/**
 * Base class for OneToMany and ManyToOne Attributes.
 */
abstract class Cardinality
{
    /**
     * Initialize with the two related classes, left and right.  The
     * cardinality is from left to right, like a RDBMS.
     *
     * @param string $left - The left Data Object class.
     * @param string $right - The right, related Data Object class.
     */
    public function __construct(
        protected string $left,
        protected string $right,
    ) {}

    /**
     * Get an instance of the left class.
     *
     * @return object A Data Object instance.
     */
    public function getLeftInstance(): object
    {
        return new $this->left;
    }

   /**
    * Get an instance of the right class.
    *
    * @return object A Data Object instance.
    */
    public function getRightInstance(): object
    {
        return new $this->right;
    }

    /**
     * Describe the relationship from left to right.
     *
     * @return string
     */
    abstract public function describe(): string;
}
