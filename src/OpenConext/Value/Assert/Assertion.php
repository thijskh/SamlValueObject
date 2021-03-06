<?php

namespace OpenConext\Value\Assert;

use Assert\Assertion as BaseAssertion;

/**
 * @method static void nullOrNonEmptyString($value, $message = null, $propertyPath = null)
 * @method static void allNonEmptyString($value, $message = null, $propertyPath = null)
 * @method static void allValidRegularExpression($value, $message = null, $propertyPath = null)
 * @method static void allKeysExist($value, $data, $message = null, $propertyPath = null)
 */
class Assertion extends BaseAssertion
{
    const INVALID_NON_EMPTY_STRING   = 501;
    const INVALID_REGULAR_EXPRESSION = 502;
    const INVALID_CALLABLE           = 503;

    protected static $exceptionClass = 'OpenConext\Value\Exception\InvalidArgumentException';

    /**
     * @param string $value
     * @param string $propertyPath
     * @return void
     */
    public static function nonEmptyString($value, $propertyPath)
    {
        if (!is_string($value) || trim($value) === '') {
            $message = 'Expected non-empty string for "%s", "%s" given';

            throw static::createException(
                $value,
                sprintf($message, $propertyPath, static::stringify($value)),
                static::INVALID_NON_EMPTY_STRING,
                $propertyPath
            );
        }
    }

    /**
     * @param $regularExpression
     * @param $propertyPath
     * @return void
     */
    public static function validRegularExpression($regularExpression, $propertyPath)
    {
        $pregMatchErrored = false;
        set_error_handler(
            function () use (&$pregMatchErrored) {
                $pregMatchErrored = true;
            }
        );

        preg_match($regularExpression, 'some test string');

        restore_error_handler();

        if ($pregMatchErrored || preg_last_error()) {
            throw static::createException(
                $regularExpression,
                sprintf('The pattern "%s" is not a valid regular expression', self::stringify($regularExpression)),
                static::INVALID_REGULAR_EXPRESSION,
                $propertyPath
            );
        }
    }

    /**
     * @param array       $requiredKeys
     * @param array       $value
     * @param null|string $message
     * @param null|string $propertyPath
     * @return void
     */
    public static function keysExist(array $value, array $requiredKeys, $message = null, $propertyPath = null)
    {
        foreach ($requiredKeys as $requiredKey) {
            self::keyExists($value, $requiredKey, $message, $propertyPath);
        }
    }

    /**
     * @param mixed       $value
     * @param null|string $message
     * @param string      $propertyPath
     */
    public static function isCallable($value, $propertyPath, $message = null)
    {
        $message = $message ?: 'Expected a callable for "%s", got a "%s"';
        if (!is_callable($value)) {
            throw static::createException(
                $value,
                sprintf($message, $propertyPath, static::stringify($value)),
                static::INVALID_CALLABLE,
                $propertyPath
            );
        }
    }
}
