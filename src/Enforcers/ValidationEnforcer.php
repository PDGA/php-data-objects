<?php

namespace PDGA\DataObjects\Enforcers;

use \ReflectionClass;
use \ReflectionAttribute;

use PDGA\DataObjects\Validators\BoolValidator;
use PDGA\DataObjects\Validators\DateValidator;
use PDGA\DataObjects\Validators\FloatValidator;
use PDGA\DataObjects\Validators\IntValidator;
use PDGA\DataObjects\Validators\NotBlankValidator;
use PDGA\DataObjects\Validators\NotNullValidator;
use PDGA\DataObjects\Validators\SequentialArrayValidator;
use PDGA\DataObjects\Validators\StringValidator;
use PDGA\DataObjects\Validators\Validator;
use PDGA\Exception\ValidationListException;

class ValidationEnforcer
{
    /**
     * Enforces the validation of the properties of an object against a class definition.
     *
     * @param array|object $object - The values to validate.
     * @param string $className - The name of the class to validate against.
     *
     * @throws ValidationListException - Throws a ValidationListException which
     * includes a cumulative list of errors encountered during validation for each
     * property on the object.
     */
    public function enforce(mixed $object, string $className): void
    {
        $arr = is_array($object) ? $object : (array) $object;
        $validationErrors = new ValidationListException();
        $metadata = $this->getValidationMetadata($className);

        // If an incoming property is not included on the
        // object, throw an error for stricter enforcement.
        $nonExistentProperties = $this->checkForPropertiesNotOnDataObject($metadata, $arr);

        foreach ($nonExistentProperties as $badProperty) {
            $validationErrors->addError(
                $this->getErrorMessage($badProperty),
                $badProperty,
            );
        }

        //For each property defined on the class.
        foreach ($metadata as $propName => $propMeta) {
            // If the propName from the data-object is not included
            // on the incoming object, don't attempt to validate it.
            if ($this->propIsUndefined($arr, $propName)) {
                continue;
            }

            //Attempt to validate each property that is on the object.
            foreach ($propMeta['validators'] as $validator) {
                if (!$validator->validate($arr[$propName])) {
                    $validationErrors->addError(
                        $validator->getErrorMessage($propName),
                        $propName,
                        null,
                        $arr[$propName],
                    );
                }
            }
        }

        if (count($validationErrors->getErrors())) {
            throw $validationErrors;
        }
    }

    /**
     * Returns an associative array with key/value pairs as follows:
     *   key: a property fo the provided class
     *   value: an associative array containing two keys; a 'reflectionProperty' which
     *     is an object of type ReflectionProperty so some metadata about the property
     *     can be referenced in other functions (such as nullability of the property)
     *     and 'validators' which is an array of instantiated Validators for every
     *     validation that the given property has defined on it.
     *
     * @param string className - The name of the class for which the validation metadata
     * is to be created.
     * @return array See above description.
     */
    public function getValidationMetadata(string $className): array
    {
        $validators = [
            "int"      => new IntValidator(),
            "string"   => new StringValidator(),
            "bool"     => new BoolValidator(),
            "float"    => new FloatValidator(),
            "DateTime" => new DateValidator(),
            "notNull"  => new NotNullValidator(),
            "array"    => new SequentialArrayValidator(),
        ];

        $metadata = [];

        $ref   = new ReflectionClass($className);
        $props = $ref->getProperties();

        //For each property defined on the class.
        foreach ($props as $prop) {
            $propName    = $prop->getName();
            $propType    = $prop->getType()->getName();
            $propAttrs   = $prop->getAttributes(Validator::class, ReflectionAttribute::IS_INSTANCEOF);
            $propCanNull = $prop->getType()->allowsNull();

            $metadata[$propName] = ['reflectionProperty' => $prop, 'validators' => []];
            $validator = array_key_exists($propType, $validators) ? $validators[$propType] : null;

            if (!is_null($validator)) {
                $metadata[$propName]['validators'][] = $validator;
            }

            if (!$propCanNull) {
                $metadata[$propName]['validators'][] = $validators['notNull'];
            }

            //String-type properties cannot be blank.
            if ($propType === 'string') {
                $metadata[$propName]['validators'][] = new NotBlankValidator();
            }

            //If the property has any validation attributes make sure they are included.
            foreach ($propAttrs as $attr) {
                $metadata[$propName]['validators'][] = $attr->newInstance();
            }
        }

        return $metadata;
    }

    /**
     * Returns true if the property is defined on the object.
     *
     * @param array|object $object - Either an object or an array on which to determine
     * if the property is defined.
     * @param string $property - The property being looked for.
     * @return bool Returns true if the property is defined on the object.
     */
    public function propIsDefined(mixed $object, string $property): bool
    {
        $arr = is_array($object) ? $object : (array) $object;

        return array_key_exists($property, $arr);
    }

    /**
     * Returns true if the property is defined as null on the object.
     *
     * @param array|object $object - Either an object or an array on which to determine
     * if the property is defined as null.
     * @param string $property - The property being looked at.
     * @return bool Returns true if the property is defined as null on the object.
     */
    public function propIsNull(mixed $object, string $property): bool
    {
        $arr = is_array($object) ? $object : (array) $object;

        return $this->propIsDefined($arr, $property) && is_null($arr[$property]);
    }

    /**
     * Returns the inverse of the propIsDefined function.  See above.
     *
     * @param array|object $object - Either an object or an array on which to determine
     * if the property is not defined.
     * @param string $property - The property being looked for.
     * @return bool Returns true if the property is not defined on the object.
     */
    public function propIsUndefined(mixed $object, string $property): bool
    {
        return !$this->propIsDefined($object, $property);
    }

    /**
     * Returns true if the property is not defined as null on the object.
     *
     * @param array|object $object - Either an object or an array on which to determine
     * if the property is not defined as null.
     * @param string $property - The property being looked for.
     * @return bool Returns true if the property is not defined as null on the object.
     */
    public function propIsNotNull(mixed $object, string $property): bool
    {
        $arr = is_array($object) ? $object : (array) $object;

        return $this->propIsDefined($arr, $property) && !is_null($arr[$property]);
    }

    private function checkForPropertiesNotOnDataObject(array $metadata, array $arr): array
    {
        $validationMetadataPropertyKeys = array_keys($metadata);
        $incomingObjectPropertyKeys = array_keys($arr);

        return array_diff($incomingObjectPropertyKeys, $validationMetadataPropertyKeys);
    }

    /**
     * Formatted error message when a property name
     * is passed in but not part of the data-object.
     *
     * @param string $propName
     * @return string
     */
    private function getErrorMessage(string $propName): string
    {
        return "'{$propName}' does not exist on the data-object.";
    }
}
