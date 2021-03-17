<?php
declare(strict_types=1);

namespace LdapDn;

use ArrayAccess, Iterator;
use Countable;
use LdapDn\Exceptions\AttributeNotFoundException;
use LdapDn\Exceptions\DnNotFoundException;
use LdapDn\Exceptions\MultipleAttributesReturnedException;
use LdapDn\Exceptions\NotImplementedException;

/**
 * Object representation of an LDAP Distinguished Name
 */
class Dn implements ArrayAccess, Iterator, Countable
{
    /** @var Rdn[] $rdns */
    private $rdns;

    /** @var int $index Iterator’s index of the current RDN */
    private $index = 0;

    /**
     * Construct a {@see Dn} with the specified {@see Rdn}s
     *
     * @param Rdn[] $rdns
     */
    public function __construct(array $rdns)
    {
        $this->rdns = $rdns;
    }

    /**
     * Construct a {@see Dn} from a string
     *
     * @param string $string DN string
     * @return static
     */
    public static function fromString(string $string): self
    {
        $rdnStrings = split_on_unescaped(',', $string);
        /** @var Rdn[] $rdns */
        $rdns = [];

        foreach ($rdnStrings as $rdnString) {
            $rdns[] = Rdn::fromString($rdnString);
        }

        return new static(array_reverse($rdns));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return join(',', array_reverse($this->rdns));
    }

    /**
     * @param string $attributeName
     * @return string
     * @throws MultipleAttributesReturnedException
     */
    public function getValue(string $attributeName): string
    {
        $dn = $this->filter($attributeName);

        $count = count($dn);

        if ($count === 0) {
            throw AttributeNotFoundException::forName($attributeName);
        }

        if ($count > 1) {
            throw MultipleAttributesReturnedException::forName($attributeName);
        }

        return $dn[0][$attributeName]->getValue();
    }

    /**
     * Filter the {@see Dn} by {@see Attribute} name
     *
     * @param string $attributeName
     * @return self New {@see Dn} with matching {@see Attribute}s
     */
    public function filter(string $attributeName): self
    {
        /** @var Rdn[] $rdns */
        $rdns = [];

        foreach ($this->rdns as $rdn) {
            if (isset($rdn[$attributeName])) {
                $rdns[] = new Rdn([$rdn[$attributeName]]);
            }
        }

        return new self($rdns);
    }

    /**
     * Construct a new {@see Dn} with the specified {@see Dn} removed
     *
     * @param Dn $dn
     * @return self New {@see Dn} with the specified {@see Dn} removed
     */
    public function withRemoved(self $dn): self
    {
        $startIndex = $this->getRdnIndex($dn[0]);

        $removeIndexes = [];

        foreach ($dn->rdns as $index => $rdn) {
            if ($this[$startIndex + $index] != $rdn) {
                throw DnNotFoundException::forDn($dn);
            }

            $removeIndexes[] = $startIndex + $index;
        }

        $rdns = [];

        foreach ($this->rdns as $index => $rdn) {
            if (in_array($index, $removeIndexes)) {
                continue;
            }

            $rdns[] = $rdn;
        }

        return new self($rdns);
    }

    /**
     * Get the parent {@see Dn}
     *
     * @return static
     */
    public function getParent(): self
    {
        $rdns = $this->rdns;

        array_pop($rdns);

        return new self($rdns);
    }

    /**
     * Get the index of the specified {@see Rdn}
     *
     * @param Rdn $rdn
     * @return int
     */
    private function getRdnIndex(Rdn $rdn): int
    {
        return array_search($rdn, $this->rdns) ?: 0;
    }

    // ArrayAccess implementation

    public function offsetExists($offset): bool
    {
        return isset($this->rdns[$offset]);
    }

    public function offsetGet($offset): Rdn
    {
        return $this->rdns[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new NotImplementedException("Setting RDNs is not allowed");
    }

    public function offsetUnset($offset): void
    {
        throw new NotImplementedException("Unsetting RDNs is not allowed");
    }

    // Iterator implementation

    public function current(): Rdn
    {
        return $this->rdns[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function valid(): bool
    {
        return isset($this->rdns[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    // Countable implementation

    /**
     * @return int The number of {@see Rdn}s in this {@see Dn}
     */
    public function count(): int
    {
        return count($this->rdns);
    }
}
