<?php

namespace PDGA\DataObjects\Enforcers;

use \ReflectionAttribute;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ModelValidationEnforcer;
use PDGA\Exception\ValidationListException;

class MutateModelValidationEnforcer extends ModelValidationEnforcer
{
    public function enforce(mixed $object, string $className): void
    {
        $arr = is_array($object) ? $object : (array) $object;
        $validationErrors = new ValidationListException();
        $metadata = $this->getValidationMetadata($className);

        //Do the type and attribute validations first.
        try
        {
            parent::enforce($arr, $className);
        }
        catch (ValidationListException $e)
        {
            //Keep any validation errors and throw them later, with possible additions.
            $validationErrors = $e;
        }

        foreach ($metadata as $propName => $propMeta)
        {
            $propAttrs = $propMeta['reflectionProperty']->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach($propAttrs as $attr)
            {
                $column  = $attr->newInstance();
                $isPrimary = $column->getIsPrimary();

                //If the column is a primary key it must be present and not null.
                if ($isPrimary &&
                    (
                        $this->propIsUndefined($arr, $propName) ||
                        $this->propIsNull($arr, $propName)
                    )
                )
                {
                    $validationErrors->addError("Primary key columns are required and should not be null.", $propName);
                }
            }
        }

        if (count($validationErrors->getErrors()))
        {
            throw $validationErrors;
        }
    }
}
