<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\Exception\ValidationListException;

use ReflectionProperty;
use ReflectionException;

class ModelInstantiator
{
    /**
     * Converts an associative array to a validated instance of a data object class.
     *
     * @param array  $arr   an array of values corresponding to the public properties of the data object.
     * @param string $class Fully-qualified class name of the data object.
     *
     * @throws ReflectionException
     * @throws ValidationListException
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

        // Assign public properties.
        foreach ($this->dataObjectPropertyColumns($class) as $property => $column)
        {
            // Ignore properties which are not specified in the incoming array.
            if ($enforcer->propIsUndefined($arr, $property))
            {
                continue;
            }

            $instance->{$property} = $arr[$property];
        }

        return $instance;
    }

    /**
     * Converts a data object to an associative array with keys matching database fields for the
     * corresponding database model.
     *
     * @param object $data_object An instance of a hydrated data object.
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
     * Converts an associative array from a database model to a data object instance.
     *
     * @param array  $db_model An associative array from a database model.
     * @param string $class    The class name of the corresponding data object.
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
     * Converts a data object instance to an array.
     *
     * @param object $data_object An instance of a hydrated data object.
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectToArray(
        object $data_object
    ): array
    {
        $array = [];

        // Set all Column-attributed properties to a key-value in the outgoing array.
        foreach ($this->dataObjectPropertyColumns($data_object::class) as $property => $column)
        {
            $array[$property] = $data_object->{$property};
        }

        return $array;
    }

    /**
     * Returns an array of all properties of a data object that have a Column attribute.
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
