<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Cardinality;
use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\Exception\ValidationException;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use ReflectionException;

class ModelInstantiator
{
    /**
     * Converts an associative array to a validated instance of a Data Object class.
     *
     * @param array  $arr   an array of values corresponding to the public properties of the Data Object.
     * @param string $class Fully-qualified class name of the Data Object.
     *
     * @throws ValidationListException
     * @throws ValidationException
     * @return object
     */
    public function arrayToDataObject(
        array  $arr,
        string $class
    ): object
    {
        // Validate the array, throws ValidationListException if something is invalid.
        $enforcer = new ValidationEnforcer();
        $enforcer->enforce($arr, $class);

        $instance = new $class();

        // This holds information about OneToMany and ManyToOne relationships.
        $cardinalities = $this->dataObjectPropertyCardinalities($class);

        // Assign public properties.
        foreach ($this->dataObjectProperties($class) as $property)
        {
            // Ignore properties which are not specified in the incoming array,
            // and properties which define relationships to other Data Objects.
            if (
                $enforcer->propIsDefined($arr, $property) &&
                !array_key_exists($property, $cardinalities)
            )
            {
                $instance->{$property} = $arr[$property];
            }
        }

        // Assign related Data Objects based on Cardinality attributes.
        foreach ($cardinalities as $property => $card)
        {
            if ($enforcer->propIsUndefined($arr, $property))
            {
                continue;
            }

            // Map to an nested array of Data Objects.
            if ($card->getDescription() === 'OneToMany')
            {
                $instances = [];

                foreach ($arr[$property] as $relation)
                {
                    $instances[] = $this->arrayToDataObject(
                        $relation,
                        $card->getRelationClass(),
                    );
                }

                $instance->{$property} = $instances;
            }
            // ManyToOne: Map to a single nested Data Object.
            else
            {
                if (!is_array($arr[$property]))
                {
                    throw new ValidationException("{$property} must be an associative array.");
                }

                $instance->{$property} = $this->arrayToDataObject(
                    $arr[$property],
                    $card->getRelationClass(),
                );
            }
        }

        return $instance;
    }

    /**
     * Converts a Data Object to an associative array with keys matching database fields for the
     * corresponding database model.
     *
     * @param object $data_object An instance of a hydrated Data Object.
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectToDatabaseModel(
        object $data_object
    ): array
    {
        $model_array = [];

        // Loop through all Column-attributed properties of the object.
        foreach ($this->dataObjectPropertyColumns($data_object::class) as $property => $column)
        {
            // Ignore unset properties.
            if (!isset($data_object->{$property}))
            {
                continue;
            }

            // Assign key based on the name property of the Column attribute. Apply value converter when applicable.
            $model_array[$column->getName()] = $this->convertPropertyOnSave(
                $column,
                $data_object->{$property}
            );
        }

        return $model_array;
    }

    /**
     * Converts an associative array from a database model to a Data Object instance.
     *
     * @param array  $db_model An associative array from a database model.
     * @param string $class    The class name of the corresponding Data Object.
     *
     * @throws ReflectionException
     * @return object
     */
    public function databaseModelToDataObject(
        array  $db_model,
        string $class
    ): object
    {
        $data_object = new $class();

        // Set all Column-attributed properties to the corresponding database column value.
        foreach ($this->dataObjectPropertyColumns($class) as $property => $column)
        {
            // Set property; apply value converter when applicable.
            $data_object->{$property} = $this->convertPropertyOnRetrieve(
                $column,
                $db_model[$column->getName()],
            );
        }

        return $data_object;
    }

    /**
     * Converts a Data Object instance to an array.
     *
     * @param object $data_object An instance of a hydrated Data Object.
     *
     * @return array
     */
    public function dataObjectToArray(
        object $data_object
    ): array
    {
        // Internal driver function that recursively converts an object to an
        // array and accepts a mixed-type argument.
        $to_array = function(mixed $data_obj) use (&$to_array)
        {
            if (is_null($data_obj) || is_scalar($data_obj))
            {
                return $data_obj;
            }

            // $data_obj is an array or object.  Cast to array, then cast each
            // element recursively.
            $arr = (array) $data_obj;

            foreach ($arr as &$ele)
            {
                $ele = $to_array($ele);
            }

            return $arr;
        };

        return $to_array($data_object);
    }

    /**
     * Returns an array of all properties of a Data Object that have a Column attribute.
     * The keys are the property names and the values are the Column attribute instance.
     *
     * @param string $class
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectPropertyColumns(
        string $class
    ): array
    {
        $columns = [];

        // Loop through all properties of the class; we use get_class_vars to include unassigned properties.
        foreach (array_keys(get_class_vars($class)) as $property)
        {
            // Find the Column attribute for the property.
            $property_reflection = new ReflectionProperty($class, $property);
            $attribute           = $property_reflection->getAttributes(Column::class);

            // If there is no Column attribute, skip this property.
            if (!$attribute)
            {
                continue;
            }

            $columns[$property] = $attribute[0]->newInstance();
        }

        return $columns;
    }

    /**
     * Returns an array of all properties of a Data Object that have a
     * Cardinality attribute, either OneToMany or ManyToOne.  Each key in the
     * returned array is a Data Object property name, and the value is a
     * Cardinality instance.
     *
     * @param string $class
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectPropertyCardinalities(
        string $class
    ): array
    {
        $props = (new ReflectionClass($class))->getProperties();
        $cards = [];

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

            $cards[$prop->getName()] = $attrs[0]->newInstance();
        }

        return $cards;
    }

    /**
     * Returns all properties of a Data Object class.
     *
     * @param string $class
     *
     * @return array
     */
    public function dataObjectProperties(
        string $class
    ): array
    {
        // Return all properties of the object including unassigned properties.
        return array_keys(get_class_vars($class));
    }

    /**
     * Converts a property for saving if there is a 'converter' value of its Attribute.
     *
     * @param Column $column
     * @param mixed  $property
     *
     * @return mixed
     */
    public function convertPropertyOnSave(
        Column $column,
        mixed  $property,
    ): mixed
    {
        // No Converter for this Column; return the original value.
        if (!$column->getConverter())
        {
            return $property;
        }

        return $column->getConverter()->onSave($property);
    }

    /**
     * Converts a property for retrieval if there is a 'converter' value of its Attribute.
     *
     * @param Column $column
     * @param mixed  $property
     *
     * @return mixed
     */
    public function convertPropertyOnRetrieve(
        Column $column,
        mixed  $property,
    ): mixed
    {
        // No Converter for this Column; return the original value.
        if (!$column->getConverter())
        {
            return $property;
        }

        return $column->getConverter()->onRetrieve($property);
    }
}
