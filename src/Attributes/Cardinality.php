<?php

namespace PDGA\DataObjects\Attributes;

use Attribute;

/**
 * Base class for OneToMany and ManyToOne Attributes.
 */
abstract class Cardinality
{
    /**
     * Initialize with a related class, relation.  The cardinality is from left
     * to right, like a RDBMS, so the class that uses this attribute is like
     * the left table, and the relation is like the right table.
     *
     * @param string $relation - The right, related Data Object class.
     * @param string $alias    - In query results, this is the name of the
     * property where the related data are set.
     */
    public function __construct(
        protected string $relation,
        protected string $alias,
    ) {
    }

   /**
    * Get the fully-qualified path of the relation.
    *
    * @return string Class path.
    */
    public function getRelationClass(): string
    {
        return $this->relation;
    }

   /**
    * Get an instance of the relation (right) class.
    *
    * @return object A Data Object instance.
    */
    public function getRelationInstance(): object
    {
        return new $this->relation;
    }

    /**
     * Get the alias of the related table in query results.
     *
     * @return string Relationship alias in query results.
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Describe the relationship from left to right.
     *
     * @return string
     */
    abstract public function getDescription(): string;
}
