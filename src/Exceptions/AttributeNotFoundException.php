<?php
declare(strict_types=1);

namespace LdapDn\Exceptions;

use Throwable;

/**
 * Thrown when an attribute lookup fails because the attribute doesn't exist in the {@see Dn}
 */
class AttributeNotFoundException extends Exception
{
    /**
     * Instantiate the exception for a given attribute name
     *
     * @param string $attribute
     * @param int $code
     * @param Throwable|null $previous
     * @return static
     */
    public static function forName($attribute = "", $code = 0, Throwable $previous = null): self
    {
        return new self("Attribute ${attribute} not found", $code, $previous);
    }
}
