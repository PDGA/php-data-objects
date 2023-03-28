<?php

/**
 * All concrete validators should implement this interface.
 *
 * function validate(mixed $val): boolean This returns true if $val is valid; else false.
 *
 * function getErrorMessage(string $propName): string A function that returns an error
 * message if validation fails. $propName is the name of the field that failed validation.
 * For example, 'The "email" field must be a valid string.' might be an appropriate error
 * message for a StringValidator.
 */
interface Validator {
    public function validate(mixed $val): boolean;
    public function getErrorMessage(string $propName): string;
}
