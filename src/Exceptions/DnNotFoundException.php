<?php
declare(strict_types=1);

namespace LdapDn\Exceptions;

use LdapDn\Dn;
use Throwable;

/**
 * Thrown when a {@see Dn} lookup fails because the specified {@see Dn} is not found
 * in another {@see Dn}
 */
class DnNotFoundException extends Exception
{
    /**
     * Instantiate the exception for the specified {@see Dn}
     *
     * @param Dn $dn
     * @param int $code
     * @param Throwable|null $previous
     * @return static
     */
    public static function forDn(Dn $dn, $code = 0, Throwable $previous = null): self
    {
        return new self("Dn '${dn}' not found", $code, $previous);
    }
}
