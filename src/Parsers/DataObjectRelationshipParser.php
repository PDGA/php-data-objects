<?php

namespace PDGA\DataObjects\Parsers;

use PDGA\DataObjects\Models\ReflectionContainer;
use PDGA\Exception\ValidationException;

class DataObjectRelationshipParser
{
    public function __construct(
        private readonly ReflectionContainer $reflection_container = new ReflectionContainer()
    ) {
    }

    /**
     * Parses an array of relationship names and returns an array of validated relationship names.
     * The specified relationships are validated to exist against the aliases of their cardinalities.
     * The expected convention is that this alias matches the name of the relationship defined on the data Model.
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
    ): array {
        if (empty($relationships_to_parse)) {
            return [];
        }

        $relationships_to_validate_by_original = $this->getUniqueRelationshipsByOriginal($relationships_to_parse);
        $validated_relationships = [];
        $invalid_relationships = [];

        foreach ($relationships_to_validate_by_original as $relationship => $relationship_lower) {
            try {
                $validated_relationships[] = $this->getValidatedRelationship(
                    $relationship_lower,
                    $data_object_class
                );
            } catch (ValidationException) {
                // Capture all invalid relationships to produce a more useful error message
                $invalid_relationships[] = $relationship;
            }
        }

        if (!empty($invalid_relationships)) {
            throw new ValidationException("Invalid relationships - " . implode(',', $invalid_relationships));
        }

        return $validated_relationships;
    }

    /**
     * Returns the validated name of the relationship.
     * The specified relationship is validated to exist against the alias of the cardinality. The expected convention
     * is that this alias matches the name of the relationship defined on the data Model.
     *
     * Supports nested relationships using dot syntax - For example: parent.child.grandchild
     *
     * @param string $relationship_to_validate_lower Name of the relationship as lowercase
     * @param string $data_object_class
     * @return string
     * @throws ValidationException
     * @throws \ReflectionException
     */
    private function getValidatedRelationship(
        string $relationship_to_validate_lower,
        string $data_object_class,
        array $applied_cardinalities = []
    ): string {
        // Separate the parent relationship from the descendants
        $aliases_to_validate = explode('.', $relationship_to_validate_lower, 2);
        $alias_to_validate_lower = trim($aliases_to_validate[0]);

        $valid_cardinalities_by_alias_lower = $this->getValidCardinalitiesKeyedByAliasLower($data_object_class);

        // The specified alias does not exist as a known relationship alias
        // or an equivalent cardinality has already been used making it a duplicate and invalid
        if (
            !key_exists($alias_to_validate_lower, $valid_cardinalities_by_alias_lower)
            || in_array($valid_cardinalities_by_alias_lower[$alias_to_validate_lower], $applied_cardinalities)
        ) {
            throw new ValidationException();
        }

        $applied_cardinalities[] = $valid_cardinalities_by_alias_lower[$alias_to_validate_lower];

        if (count($aliases_to_validate) > 1) {
            $relation_class = $valid_cardinalities_by_alias_lower[$alias_to_validate_lower]->getRelationClass();

            // Build the validated nested relationship name using recursive call
            return $valid_cardinalities_by_alias_lower[$alias_to_validate_lower]->getAlias()
                   . '.'
                   . $this->getValidatedRelationship(
                       $aliases_to_validate[1],
                       $relation_class,
                       $applied_cardinalities
                   );
        }

        return $valid_cardinalities_by_alias_lower[$alias_to_validate_lower]->getAlias();
    }

    /**
     * @param string $data_object_class
     * @return array
     * @throws \ReflectionException
     */
    private function getValidCardinalitiesKeyedByAliasLower(string $data_object_class): array
    {
        $valid_relationship_cardinalities = $this->getRelationshipCardinalitiesForDataObject($data_object_class);
        $cardinality_aliases = array_map(
            fn($cardinality) => $cardinality->getAlias(),
            $valid_relationship_cardinalities
        );

        // Produces an array of valid relationships keyed by the lowercase name of the relationship.
        // This allows us to ignore differences in casing.
        return array_combine(
            array_map(fn($alias) => strtolower($alias), $cardinality_aliases),
            $valid_relationship_cardinalities
        );
    }

    /**
     * For a given Data Object class, this will provide the names of the Cardinality relationships that are defined.
     *
     * @param string $data_object_class
     * @return array
     * @throws \ReflectionException
     */
    private function getRelationshipCardinalitiesForDataObject(string $data_object_class): array
    {
        $properties = $this->reflection_container->dataObjectProperties($data_object_class);

        return $this->reflection_container->dataObjectPropertyCardinalities($properties);
    }

    /**
     * Generates an array of lowercase relationship names keys by the original name
     * Duplicate values are removed.
     *
     * @param array $relationships_to_parse
     * @return array
     */
    private function getUniqueRelationshipsByOriginal(array $relationships_to_parse): array
    {
        return array_unique(
            array_combine(
                $relationships_to_parse,
                array_map("strtolower", $relationships_to_parse)
            )
        );
    }
}
