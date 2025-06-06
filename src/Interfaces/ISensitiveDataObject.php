<?php

namespace PDGA\DataObjects\Interfaces;

interface ISensitiveDataObject
{
    /**
     * This will be implemented by any data object that needs to have the values for
     * sensitive fields removed. The implementation of this method for the
     * data object should unset the values of the sensitive fields.
     *
     * @return void
     */
    public function cleanseSensitiveFields(): void;
}
