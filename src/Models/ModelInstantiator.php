<?php

namespace PDGA\DataObjects\Models;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\DataObjects\Interfaces\IDatabaseModel;
use PDGA\DataObjects\Models\ReflectionContainer;
use PDGA\Exception\InvalidRelationshipDataException;
use PDGA\Exception\ValidationException;

use \Datetime;
use ReflectionException;

class ModelInstantiator
{
    public function __construct(private ReflectionContainer $reflection_container = new ReflectionContainer())
    {}

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

        $property_reflection = $this->reflection_container
            ->dataObjectProperties($class);

        // This holds information about OneToMany and ManyToOne relationships.
        $cardinalities = $this->reflection_container
            ->dataObjectPropertyCardinalities($property_reflection);

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
        $enforcer    = new ValidationEnforcer();

        $property_reflection = $this->reflection_container
            ->dataObjectProperties($data_object::class);

        $column_reflection = $this->reflection_container
            ->dataObjectPropertyColumns($property_reflection);

        // Loop through all Column-attributed properties of the object.
        foreach ($column_reflection as $property => $column)
        {
            // Ignore undefined properties.
            if ($enforcer->propIsUndefined($data_object, $property))
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
     * @param IDatabaseModel $db_model An object which implements the IDatabaseModel interface.
     * @param string $class The class name of the corresponding Data Object.
     *
     * @throws ReflectionException
     * @throws InvalidRelationshipDataException
     * @return object
     */
    public function databaseModelToDataObject(
        IDatabaseModel $db_model,
        string $class
    ): object
    {
        $data_object         = new $class();
        $enforcer            = new ValidationEnforcer();
        $property_reflection = $this->reflection_container
            ->dataObjectProperties($class);
        $column_reflection   = $this->reflection_container
            ->dataObjectPropertyColumns($property_reflection);

        $model_attributes = $db_model->getAttributes();

        // Set all Column-attributed properties to the corresponding database column value.
        foreach ($column_reflection as $property => $column)
        {
            $col_name = $column->getName();

            if ($enforcer->propIsDefined($model_attributes, $col_name))
            {
                // Set property; apply value converter when applicable.
                $data_object->{$property} = $this->convertPropertyOnRetrieve(
                    $column,
                    $model_attributes[$col_name],
                );
            }
        }

        // Now handle nested relationship data, recursively.
        $cardinality_reflection = $this->reflection_container
            ->dataObjectPropertyCardinalities($property_reflection);

        if (!count($cardinality_reflection))
        {
            return $data_object;
        }

        $model_relations = $db_model->getRelations();

        foreach ($cardinality_reflection as $property => $card)
        {
            // "alias" is the name of the property on the DB model, which comes
            // from the Cardinality attribute.
            $alias = $card->getAlias();

            if ($enforcer->propIsUndefined($model_relations, $alias))
            {
                continue;
            }

            // Related Data Object class.
            $relation_class = $card->getRelationClass();

            if ($card->getDescription() === 'OneToMany')
            {
                $data_object->{$property} = [];

                foreach ($model_relations[$alias] as $relation_db_model)
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
                // If the value is null make sure it's allowed to be null.
                if (is_null($model_relations[$alias]))
                {
                    $reflection_index    = array_search($property, array_column($property_reflection, 'name'));
                    $reflection_property = $property_reflection[$reflection_index];

                    if ($reflection_property->getType()->allowsNull())
                    {
                        $data_object->{$property} = null;
                        continue;
                    }

                    // Null not allowed, throw exception.
                    throw new InvalidRelationshipDataException("{$alias} relationship must not be null.");
                }

                $data_object->{$property} = $this->databaseModelToDataObject(
                    $model_relations[$alias],
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
        if (!$column->getConverter())
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
