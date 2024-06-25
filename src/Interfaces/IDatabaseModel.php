<?php

namespace PDGA\DataObjects\Interfaces;

interface IDatabaseModel {
    /**
     * Get the attributes of the database model.
     *
     * @return array The attributes of the database model.
     */
    public function getAttributes();

    /**
     * Get the relations of the database model.
     *
     * @return array The relations of the database model.
     */
    public function getRelations();
}
