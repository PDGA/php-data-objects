<?php

namespace PDGA\DataObjects\Parsers;

use PDGA\DataObjects\Models\ReflectionContainer;
use PDGA\Exception\ValidationException;

class DataObjectRelationshipParser
{
    public function __construct(
        private readonly ReflectionContainer $reflection_container = new ReflectionContainer()
    )
    {}

    /**
     * For a given Data Object class, this will provide the names of the Cardinality relationships that are defined.
     *
     * @param string $data_object_class
     * @return array
     * @throws \ReflectionException
     */
    public function getRelationshipAliasesForDataObject(string $data_object_class): array
    {
        $properties = $this->reflection_container->dataObjectProperties($data_object_class);
        $cardinalities = $this->reflection_container->dataObjectPropertyCardinalities($properties);

        return array_map(fn($cardinality) => $cardinality->getAlias(), $cardinalities);
    }

    /**
     * Parses a comma delimited string that specifies relationships and returns an
     * array of parsed valid relationship names.
     *
     * @param string|null $relationships_to_parse
     * @param string $data_object_class
     * @return array
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function parseRelationshipsForDataObject(
        ?string $relationships_to_parse,
        string $data_object_class
    ): array
    {
        if (empty($relationships_to_parse))
        {
            return [];
        }

        $valid_relationships = $this->getRelationshipAliasesForDataObject($data_object_class);
        $relationships_to_validate = array_unique(explode(',', $relationships_to_parse));

        // This will produce an array of valid relationships keyed by the lowercase name of the relationship which
        // allows us to perform a case-insensitive comparison below.
        $relationships_keyed_by_lower = array_combine(
            array_map('strtolower', $valid_relationships),
            $valid_relationships
        );

        $validated_relationships = [];
        $invalid_relationships = [];

        foreach ($relationships_to_validate as $relationship_to_validate)
        {
            $lower_relationship_to_check = trim(strtolower($relationship_to_validate));

            if (key_exists($lower_relationship_to_check, $relationships_keyed_by_lower))
            {
                $validated_relationships[] = $relationships_keyed_by_lower[$lower_relationship_to_check];
            }
            else
            {
                $invalid_relationships[] = $relationship_to_validate;
            }
        }

        if (!empty($invalid_relationships))
        {
            $unknown_relationships = implode(',', $invalid_relationships);

            throw new ValidationException("Unknown relationships - {$unknown_relationships}");
        }

        return $validated_relationships;
    }
}
