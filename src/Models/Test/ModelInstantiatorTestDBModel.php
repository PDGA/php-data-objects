<?php

namespace PDGA\DataObjects\Models\Test;

use PDGA\DataObjects\Interfaces\IDatabaseModel;

// Mimics an Eloquent model, which has attributes and relations arrays.
class ModelInstantiatorTestDBModel implements IDatabaseModel
{
    private $attributes = [];
    private $relations  = [];

    public function __construct(int $pdga_num, ?string $display_name = null)
    {
        $this->attributes['PDGANum'] = $pdga_num;
        $this->attributes['DisplayName'] = $display_name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    // Mimic a ManyToOne relation (object).
    public function addOneRelation(?ModelInstantiatorTestDBModel $relation): void
    {
        $this->relations['FakeHasOneRelation'] = $relation;
    }

    // Mimic a nullable ManyToOne relation (object).
    public function addNullableOneRelation(?ModelInstantiatorTestDBModel $relation): void
    {
        $this->relations['NullableFakeHasOneRelation'] = $relation;
    }

    // Mimic a OneToMany relation (array).
    public function addManyRelation(ModelInstantiatorTestDBModel $relation): void
    {
        $this->relations['FakeHasManyRelation'] = [$relation];
    }

    public function getRelations(): array
    {
        return $this->relations;
    }
}
