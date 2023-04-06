<?php

namespace PDGA\DataObjects\Enforcers;

use \ReflectionClass;

use PDGA\DataObjects\Validators\BoolValidator;
use PDGA\DataObjects\Validators\DateValidator;
use PDGA\DataObjects\Validators\IntValidator;
use PDGA\DataObjects\Validators\NotNullValidator;
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

        $validators = [
            "int"      => new IntValidator(),
            "string"   => new StringValidator(),
            "bool"     => new BoolValidator(),
            "DateTime" => new DateValidator(),
            "null"     => new NotNullValidator(),
        ];

        $arr = is_array($object) ? $object : (array) $object;
        $validationErrors = new ValidationListException();

        $ref   = new ReflectionClass($className);
        $props = $ref->getProperties();

        //For each property defined on the class.
        foreach($props as $prop)
        {
            $propName    = $prop->getName();
            $propType    = $prop->getType()->getName();
            $propAttrs   = $prop->getAttributes();
            $propCanNull = $prop->getType()->allowsNull();

            //If the class property is passed in with the object.
            if ($this->propIsDefined($arr, $propName))
            {
                //If the property is not null and a validator exists for that type make sure the type is correct.
                $validator = array_key_exists($propType, $validators) ? $validators[$propType] : null;
                if ($this->propIsNotNull($arr, $propName) && !is_null($validator))
                {
                    if (!$validator->validate($arr[$propName]))
                    {
                        $validationErrors->addError($validator->getErrorMessage($propName), $propName);
                    }
                }
                //If the property is null make sure it is allowed to be null.
                else if (!$propCanNull && $this->propIsNull($arr, $propName))
                {
                    $validationErrors->addError($validators["null"]->getErrorMessage($propName), $propName);
                    if (!is_null($validator))
                    {
                        $validationErrors->addError($validator->getErrorMessage($propName), $propName);
                    }
                }

                //If the property has any attributes make sure they are enforced.
                foreach($propAttrs as $attr)
                {
                    $validatorName = $attr->getName();
                    if (is_subclass_of($validatorName, Validator::class))
                    {
                        $attrValidator = new $validatorName(...$attr->getArguments());

                        if (!$attrValidator->validate($arr[$propName]))
                        {
                            $validationErrors->addError($attrValidator->getErrorMessage($propName), $propName);
                        }
                    }
                }
            }
        }

        if (count($validationErrors->getErrors()))
        {
            throw $validationErrors;
        }
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

        return $this->propIsDefined($arr, $property) && is_null($arr["$property"]);
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

        return $this->propIsDefined($arr, $property) && !is_null($arr["$property"]);
    }
}
