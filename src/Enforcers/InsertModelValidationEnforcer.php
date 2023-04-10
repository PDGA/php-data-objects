<?php

namespace PDGA\DataObjects\Enforcers;

use \ReflectionAttribute;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ModelValidationEnforcer;
use PDGA\Exception\ValidationListException;

class InsertModelValidationEnforcer extends ModelValidationEnforcer
{
    /**
     * Enforces the validation of the properties of an object against a class definition.
     * Then adds appropriate errors if generated fields (as defined by the Column attribute)
     * are included and/or if fields with no default value (as defined by the Column attribute)
     * are not included or are null.
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

        try
        {
            parent::enforce($arr, $className);
        }
        catch (ValidationListException $e)
        {
            $validationErrors = $e;
        }

        foreach ($metadata as $propName => $propMeta)
        {
            $propCanNull = $propMeta['reflectionProperty']->getType()->allowsNull();
            $propAttrs   = $propMeta['reflectionProperty']->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach($propAttrs as $attr)
            {
                $column    = $attr->newInstance();
                $generated = $column->getIsGenerated();
                $default   = $column->getHasDefault();

                if ($generated && $this->propIsDefined($arr, $propName))
                {
                    $validationErrors->addError("$propName should not be defined for an insert.", $propName);
                }

                if (!$generated &&
                    !$default &&
                    !$propCanNull &&
                    (
                        $this->propIsUndefined($arr, $propName) ||
                        $this->propIsNull($arr, $propName)
                    )
                )
                {
                    $validationErrors->addError("Non-nullable properties with no default value are required.", $propName);
                }
            }
        }

        if (count($validationErrors->getErrors()))
        {
            throw $validationErrors;
        }
    }
}
