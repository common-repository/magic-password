<?php

namespace TwoFAS\Account\Exception;

use TwoFAS\ValidationRules\ValidationExceptionInterface;
use TwoFAS\ValidationRules\ValidationRules;

/**
 * Validation Exceptions may contain multiple keys and rules.
 * This exception will be thrown if data sent do API doesn't match the scheme.
 *
 * @package TwoFAS\Account\Exception
 */
class ValidationException extends Exception implements ValidationExceptionInterface
{
    /**
     * @var array
     */
    private $errors = array();

    /**
     * @param array          $errors
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct(array $errors, $code = 0, Exception $previous = null)
    {
        parent::__construct('Validation exception', $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->normalizeErrors();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->errors);
    }

    /**
     * @param string $key
     * @param string $rule
     *
     * @return bool
     */
    public function hasError($key, $rule)
    {
        if (!$this->hasKey($key)) {
            return false;
        }

        return in_array($rule, $this->getError($key), true);
    }

    /**
     * @param string $key
     *
     * @return array|null
     */
    public function getError($key)
    {
        if (!$this->hasKey($key)) {
            return null;
        }

        return $this->normalizeError($this->errors[$key]);
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getBareError($key)
    {
        if (!$this->hasKey($key)) {
            return null;
        }

        return $this->errors[$key];
    }

    /**
     * @return array
     */
    private function normalizeErrors()
    {
        return array_map(
            function(array $rules) {
                return array_map(
                    function($rule) {
                        return ValidationRules::getContainingRule($rule);
                    },
                    $rules
                );
            },
            $this->errors
        );
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    private function normalizeError(array $rules)
    {
        return array_map(
            function($rule) {
                return ValidationRules::getContainingRule($rule);
            },
            $rules
        );
    }
}