<?php

namespace PDGA\DataObjects;

/**
 * All concrete validators should implement this interface.
 */
interface Validator {

    /**
     * This returns true if $val is valid; else false.
     *
     * @param mixed $val The value to validate.
     * @return boolean Returns true if $val is valid; else false.
     */
    public function validate(mixed $val): bool;

    /**
     * Called to get an error message on validation failure.
     *
     * @param string $propName The name of the field that failed validation.
     * @return an error message as a string.  For example, 'The "email" field
     * must be a valid string.' might be an appropriate error message for
     * a StringValidator.
     */
    public function getErrorMessage(string $propName): string;
}
