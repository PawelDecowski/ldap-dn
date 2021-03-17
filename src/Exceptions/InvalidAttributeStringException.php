<?php
declare(strict_types=1);

namespace LdapDn\Exceptions;

use Throwable;

/**
 * Thrown when an {@see Attribute} string is invalid
 */
class InvalidAttributeStringException extends Exception
{
    /**
     * Instantiate the exception for a given attribute string
     *
     * @param string $string
     * @param int $code
     * @param Throwable|null $previous
     * @return static
     */
    public static function forString($string = "", $code = 0, Throwable $previous = null): self
    {
        return new self("Attribute string '${string}' is invalid", $code, $previous);
    }
}
