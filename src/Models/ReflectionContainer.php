<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Cardinality;
use PDGA\DataObjects\Attributes\Column;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

class ReflectionContainer
{
    private array $class_reflection  = [];
    private array $column_reflection = [];
    private array $card_reflection   = [];

    /**
     * Returns an array of all properties of a Data Object that have a Column attribute.
     * The keys are the property names and the values are the Column attribute instance.
     *
     * @param array $props - An Array of ReflectionProperties.
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectPropertyColumns(
        array $props
    ): array
    {
        $class = $props[0]->class;

        if (array_key_exists($class, $this->column_reflection))
        {
            return $this->column_reflection[$class];
        }

        // Default the class to an empty array.
        $this->column_reflection[$class] = [];

        // Loop through all ReflectionProperties.
        foreach ($props as $property)
        {
            // Find the Column attribute for the property.
            $attribute = $property->getAttributes(Column::class);

            // If there is no Column attribute, skip this property.
            if (!$attribute)
            {
                continue;
            }

            $this->column_reflection[$class][$property->getName()] = $attribute[0]->newInstance();
        }

        return $this->column_reflection[$class];
    }

    /**
     * Returns an array of all properties of a Data Object that have a
     * Cardinality attribute, either OneToMany or ManyToOne.  Each key in the
     * returned array is a Data Object property name, and the value is a
     * Cardinality instance.
     *
     * @param array $props - An Array of ReflectionProperties.
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectPropertyCardinalities(
        array $props
    ): array
    {
        $class = $props[0]->class;

        if (array_key_exists($class, $this->card_reflection))
        {
            return $this->card_reflection[$class];
        }

        // Default the class to an empty array.
        $this->column_reflection[$class] = [];

        foreach ($props as $prop)
        {
            $attrs = $prop->getAttributes(
                Cardinality::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if (!$attrs)
            {
                continue;
            }

            $this->card_reflection[$class][$prop->getName()] = $attrs[0]->newInstance();
        }

        return $this->card_reflection[$class];
    }

    /**
     * Returns ReflectionProperties for all properties of a Data Object class.
     *
     * @param string $class
     *
     * @return array
     */
    public function dataObjectProperties(
        string $class
    ): array
    {
        if (array_key_exists($class, $this->class_reflection))
        {
            return $this->class_reflection[$class];
        }

        // Return ReflectionProperties of all properties of the object including unassigned properties.
        return $this->class_reflection[$class] = (new ReflectionClass($class))->getProperties();
    }
}

