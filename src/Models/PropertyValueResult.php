<?php

namespace PDGA\DataObjects\Models;

/**
 * Class PropertyValueResult
 * This class is used to map objects with ModelInstantiator in order to handle null vs, undefined vs empty, vs values.
 *
 * @OA\Schema(
 *     title="PropertyValueResult",
 *     description="A class to handle mapping of data objects with relationships that can vary in value."
 * )
 */
class PropertyValueResult
{
    public function __construct(
        bool $isUndefined,
        mixed $resObject = null,
    ) {
        $this->undefined = $isUndefined;
        $this->result = $resObject;
    }

    public bool $undefined;

    public mixed $result;
}
