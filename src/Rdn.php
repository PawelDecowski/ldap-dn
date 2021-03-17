<?php
declare(strict_types=1);

namespace LdapDn;

use ArrayAccess;
use Countable;
use LdapDn\Exceptions\AttributeNotFoundException;
use LdapDn\Exceptions\NotImplementedException;
use Iterator;

/**
 * Represents
 * @package DNParser
 */
class Rdn implements ArrayAccess, Iterator, Countable
{
    /** @var Attribute[] $attributes */
    private $attributes;

    /** @var int $index Iterator’s index of the current attribute  */
    private $index = 0;

    /**
     * Construct a new {@see Rdn} with the specified {@see Attribute}s
     *
     * @param Attribute[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Construct a new {@see Rdn} from a string
     *
     * @param string $string RDN string
     * @return static
     */
    public static function fromString(string $string): self
    {
        $attributeStrings = split_on_unescaped('\+', $string);
        $attributes = [];

        foreach ($attributeStrings as $attributeString) {
            $attributes[] = Attribute::fromString($attributeString);
        }

        return new self($attributes);
    }

    /**
     * Construct a new {@see Rdn} with a single attribute with the specified name and value
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public static function withNameAndValue(string $name, string $value): self
    {
        $attribute = new Attribute($name, $value);

        return new self([$attribute]);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return join('+', $this->attributes);
    }

    // ArrayAccess implementation

    public function offsetExists($offset): bool
    {
        $attribute = array_filter($this->attributes, function(Attribute $attribute) use ($offset) {
            return strtolower($attribute->getName()) === strtolower($offset);
        });

        return count($attribute) === 1;
    }

    public function offsetGet($offset): Attribute
    {
        foreach ($this->attributes as $attribute) {
            if (strtolower($attribute->getName()) === strtolower($offset)) {
                return $attribute;
            }
        }

        throw AttributeNotFoundException::forName($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new NotImplementedException("Setting attributes is not implemented");
    }

    public function offsetUnset($offset)
    {
        throw new NotImplementedException("Unsetting attributes is not implemented");
    }

    // Countable implementation

    /**
     * @return int The number of attributes in this {@see Rdn}
     */
    public function count(): int
    {
        return count($this->attributes);
    }

    // Iterator implementation

    public function current(): Attribute
    {
        return $this->attributes[$this->index];
    }

    public function next()
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->attributes[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }
}
