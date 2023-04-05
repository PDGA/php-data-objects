<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Column;

use ReflectionProperty;

class ModelInstantiator
{
    /**
     * Converts an associate array to a validated instance of a data object class.
     *
     * @param array  $arr   an array of values corresponding to the public properties of the data object.
     * @param string $class Fully-qualified class name of the data object.
     *
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
        foreach (array_keys(get_class_vars($class)) as $property)
        {
            if (!isset($arr[$property]))
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
     * @throws \ReflectionException
     *
     * @return array
     */
    public function dataObjectToDatabaseModel(
        object $data_object
    ): array
    {
        $model_array = [];

        // Loop through all assigned properties of the object.
        foreach (get_object_vars($data_object) as $property => $value)
        {
            $property_reflection = new ReflectionProperty($data_object::class, $property);

            // Get the Column attribute.
            $attribute = $property_reflection->getAttributes(Column::class);

            // Assign key based on the name property of the Column attribute.
            $model_array[$attribute[0]->newInstance()->getName()] = $value;
        }

        return $model_array;
    }

    /**
     * Converts an associative array from a database model to a data object instance.
     *
     * @param array  $db_model An associative array from a database model.
     * @param string $class    The class name of the corresponding data object.
     *
     * @throws \ReflectionException
     * @return object
     */
    public function databaseModelToDataObject(
        array  $db_model,
        string $class
    ): object
    {
        $data_object = new $class();

        // Loop through all properties of the class; we use get_class_vars to include unassigned properties.
        foreach (array_keys(get_class_vars($class)) as $property)
        {
            $property_reflection = new ReflectionProperty($class, $property);
            $attribute           = $property_reflection->getAttributes(Column::class);
            $key                 = $attribute[0]->newInstance()->getName();

            if (!isset($db_model[$key]))
            {
                continue;
            }

            $data_object->{$property} = $db_model[$key];
        }

        return $data_object;
    }

    /**
     * Converts a data object instance to an array.
     *
     * @param object $data_object An instance of a hydrated data object.
     *
     * @return array
     */
    public function dataObjectToArray(
        object $data_object
    ): array
    {
        return (array) $data_object;
    }
}
