<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Cardinality;
use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\Exception\ValidationException;

use \Datetime;
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

        $property_reflection = $this->dataObjectProperties($class);

        // This holds information about OneToMany and ManyToOne relationships.
        $cardinalities = $this->dataObjectPropertyCardinalities($property_reflection);

        // Assign public properties.
        foreach ($property_reflection as $reflection)
        {
            $property = $reflection->getName();
            $type     = $reflection->getType()->getName();
            $not_null = $enforcer->propIsNotNull($arr, $property);

            // Ignore properties which are not specified in the incoming array,
            // and properties which define relationships to other Data Objects.
            if (
                $enforcer->propIsDefined($arr, $property) &&
                !array_key_exists($property, $cardinalities)
            )
            {
                $instance->{$property} = $not_null && $type === 'DateTime'
                    ? new DateTime($arr[$property])
                    : $arr[$property];
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

        $property_reflection = $this->dataObjectProperties($data_object::class);

        // Loop through all Column-attributed properties of the object.
        foreach ($this->dataObjectPropertyColumns($property_reflection) as $property => $column)
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
        $data_object         = new $class();
        $enforcer            = new ValidationEnforcer();
        $property_reflection = $this->dataObjectProperties($class);

        // Set all Column-attributed properties to the corresponding database column value.
        foreach ($this->dataObjectPropertyColumns($property_reflection) as $property => $column)
        {
            $col_name = $column->getName();

            if ($enforcer->propIsDefined($db_model, $col_name))
            {
                // Set property; apply value converter when applicable.
                $data_object->{$property} = $this->convertPropertyOnRetrieve(
                    $column,
                    $db_model[$col_name],
                );
            }
        }

        // Now handle nested relationship data.
        foreach ($this->dataObjectPropertyCardinalities($property_reflection) as $property => $card)
        {
            // "alias" is the name of the property on the DB model, which comes
            // from the Cardinality attribute.
            $alias = $card->getAlias();

            if ($enforcer->propIsUndefined($db_model, $alias))
            {
                continue;
            }

            // Related Data Object class.
            $relation_class = $card->getRelationClass();

            if ($card->getDescription() === 'OneToMany')
            {
                $data_object->{$property} = [];

                foreach ($db_model[$alias] as $relation_db_model)
                {
                    $data_object->{$property}[] = $this->databaseModelToDataObject(
                        $relation_db_model,
                        $relation_class,
                    );
                }
            }
            // Many-to-one relationship (a single nested Data Object).
            else
            {
                $data_object->{$property} = $this->databaseModelToDataObject(
                    $db_model[$alias],
                    $relation_class,
                );
            }
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

            if ($data_obj instanceof DateTime)
            {
                return $data_obj->format(DateTime::ATOM);
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
     * @param array $props - An Array of ReflectionProperties.
     *
     * @throws ReflectionException
     * @return array
     */
    public function dataObjectPropertyColumns(
        array $props
    ): array
    {
        $columns = [];

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

            $columns[$property->getName()] = $attribute[0]->newInstance();
        }

        return $columns;
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
        // Return ReflectionProperties of all properties of the object including unassigned properties.
        return (new ReflectionClass($class))->getProperties();
    }

    /**
     * Converts a property for saving if there is a 'converter' value of its
     * Attribute and the property is not null.
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

        // If the property is null, then return null.
        if ($property === null)
        {
            return $property;
        }

        // Convert for save.
        return $column->getConverter()->onSave($property);
    }

    /**
     * Converts a property on retrieval if there is a 'converter' value of its
     * Attribute and the property is not null.
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
        if (!$column->getConverter() || $property === null)
        {
            return $property;
        }

        // If the property is null, then return null.
        if ($property === null)
        {
            return $property;
        }

        return $column->getConverter()->onRetrieve($property);
    }
}
