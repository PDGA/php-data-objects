<?php

namespace PDGA\DataObjects\Interfaces;

interface IPrivacyProtectedDataObject
{

    /**
     * This will be implemented by any data object that needs to have the values for
     * privacy protected fields removed. The implementation of this method for the
     * data object should unset the values of the protected fields.
     *
     * @return void
     */
    public function cleansePrivacyProtectedFields();
}
