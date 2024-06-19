<?php

namespace PDGA\DataObjects\Enforcers;

use \ReflectionAttribute;

use PDGA\DataObjects\Attributes\Column;
use PDGA\DataObjects\Enforcers\ValidationEnforcer;
use PDGA\DataObjects\Validators\MaxLengthValidator;

class ModelValidationEnforcer extends ValidationEnforcer
{
    /**
     * Extends the ValidationEnforcer getValidationMetadata function,
     * returning the array from the parent but adding a MaxLengthValidator
     * if a property is defined as a Column and that column has a non-null
     * value for maxLength.
     *
     * @param string $className - The name of the class for which metadata should be
     * returned.
     * @return array See ValidationEnforcer@getValidationMetadata.
     */
    public function getValidationMetadata(string $className): array
    {
        $metadata = parent::getValidationMetadata($className);

        //For each property defined on the class.
        foreach ($metadata as $propName => $propMeta) {
            $propAttrs = $propMeta['reflectionProperty']
                ->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

            //If the property has a column attribute add the max length validator if
            //max length is not null on the column.
            foreach ($propAttrs as $attr) {
                $column    = $attr->newInstance();
                $maxLength = $column->getMaxLength();

                if (!is_null($maxLength)) {
                    $metadata[$propName]['validators'][] = new MaxLengthValidator($maxLength);
                }
            }
        }

        return $metadata;
    }
}
