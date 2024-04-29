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
    public function getRelationshipCardinalitiesForDataObject(string $data_object_class): array
    {
        $properties = $this->reflection_container->dataObjectProperties($data_object_class);

        return $this->reflection_container->dataObjectPropertyCardinalities($properties);
    }

    /**
     * Parses an array of relationship names and returns an array of validated relationship names.
     *
     * @param array $relationships_to_parse
     * @param string $data_object_class
     * @return array
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function parseRelationshipsForDataObject(
        array $relationships_to_parse,
        string $data_object_class
    ): array
    {
        if (empty($relationships_to_parse))
        {
            return [];
        }

        // This will preserve the original value to be used in the exception message
        $relationships_to_validate_keyed_by_original = array_unique(
            array_combine(
                $relationships_to_parse,
                array_map("strtolower", $relationships_to_parse)
            )
        );

        $validated_relationships = [];
        $invalid_relationships = [];

        foreach ($relationships_to_validate_keyed_by_original as $relationship_to_validate_original => $relationship_to_validate_lower)
        {
            try
            {
                $validated_relationships[] = $this->getValidRelationship($relationship_to_validate_lower, $data_object_class);
            }
            catch (ValidationException)
            {
                $invalid_relationships[] = $relationship_to_validate_original;
            }
        }

        if (!empty($invalid_relationships))
        {
            $unknown_relationships = implode(',', $invalid_relationships);

            throw new ValidationException("Unknown relationships - {$unknown_relationships}");
        }

        return $validated_relationships;
    }

    /**
     * Returns the validated name of the relationship.
     * Supports nested relationships using dot syntax - For example: parent.child.grandchild
     *
     * @param string $lower_relationship_to_check
     * @param string $data_object_class
     * @return string
     * @throws ValidationException
     * @throws \ReflectionException
     */
    private function getValidRelationship(
        string $lower_relationship_to_check,
        string $data_object_class
    ): string
    {

        $nested_relationships = explode('.', trim($lower_relationship_to_check), 2);

        $valid_relationship_cardinalities = $this->getRelationshipCardinalitiesForDataObject($data_object_class);

        $cardinality_aliases = array_map(
            fn ($cardinality) => $cardinality->getAlias(),
            $valid_relationship_cardinalities
        );

        // This will produce an array of valid relationships keyed by the lowercase name of the relationship which
        // allows us to perform a case-insensitive comparison below.
        $valid_relationships_keyed_by_lower_alias = array_combine(
            array_map(fn ($alias) => strtolower($alias),$cardinality_aliases),
            $valid_relationship_cardinalities
        );

        $relationship_to_validate = trim($nested_relationships[0]);

        if (key_exists($relationship_to_validate, $valid_relationships_keyed_by_lower_alias))
        {
            if (count($nested_relationships) > 1)
            {
                $relation_class = $valid_relationships_keyed_by_lower_alias[$relationship_to_validate]->getRelationClass();

                return $valid_relationships_keyed_by_lower_alias[$relationship_to_validate]->getAlias()
                       . '.'
                       . $this->getValidRelationship($nested_relationships[1], $relation_class);
            }

            return $valid_relationships_keyed_by_lower_alias[$relationship_to_validate]->getAlias();
        }

        throw new ValidationException();
    }
}
