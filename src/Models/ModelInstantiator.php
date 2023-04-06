<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Column;

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
     * @return object
     */
    public function arrayToDataObject(
        array  $arr,
        string $class
    ): object
    {
        $instance = new $class();

        // TODO validate.

        // Assign all public properties.
        foreach ($this->dataObjectPropertyColumns($class) as $property => $column)
        {
            $instance->{$property} = $arr[$property] ?? null;
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

        // Loop through all assigned properties of the object.
        foreach ($this->dataObjectPropertyColumns($data_object::class) as $property => $column)
        {
            // Assign key based on the name property of the Column attribute.
            $model_array[$column] = $data_object->{$property};
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

        foreach ($this->dataObjectPropertyColumns($class) as $property => $column)
        {
            $data_object->{$property} = $db_model[$column] ?? null;
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

        foreach ($this->dataObjectPropertyColumns($data_object::class) as $property => $column)
        {
            $array[$property] = $data_object->{$property} ?? null;
        }

        return $array;
    }

    /**
     * Returns an array of all properties of a data object that have a Column attribute.
     * The keys are the property names and the values are the Column 'name' attribute.
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

            $columns[$property] = $attribute[0]->newInstance()->getName();
        }

        return $columns;
    }
}
