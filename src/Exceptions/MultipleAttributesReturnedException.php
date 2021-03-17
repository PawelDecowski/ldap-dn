<?php
declare(strict_types=1);

namespace LdapDn\Exceptions;

/**
 * Thrown when multiple attributes were found when exactly 1 was expected
 */
class MultipleAttributesReturnedException extends \Exception
{
    /**
     * Instantiate the exception for a given attribute name
     *
     * @param string $name
     * @param int $code
     * @param \Exception|null $previous
     * @return static
     */
    public static function forName(string $name, $code = 0, \Exception $previous = null): self
    {
        return new self("Multiple '${name}' attributes returned", $code, $previous);
    }
}
