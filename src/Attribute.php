<?php
declare(strict_types=1);

namespace LdapDn;

use LdapDn\Exceptions\InvalidAttributeStringException;

/**
 * Represents a single key-value pair in an RDN
 * 
 * Usually RDNs consist of a single key-value pair but multiple key-value pairs,
 * separated by a `+` character, are allowed.
 */
class Attribute
{
    /** @var string $name */
    private $name;
    /** @var string $value */
    private $value;

    /**
     * Construct a new {@see Attribute} with specified name and value
     * 
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name = self::unescape(self::normalize($name));
        $this->value = self::unescape(self::normalize($value));
    }

    /**
     * Construct a new {@see Attribute} from a string
     *
     * The string must be in the form `name=value` with the value escaped accordingly.
     *
     * @param $string
     * @return Attribute
     */
    public static function fromString($string): self
    {
        if (!preg_match('#(?<!\\\\)=#', $string)) {
            throw InvalidAttributeStringException::forString($string);
        }

        [$name, $value] = split_on_unescaped('=', $string);

        return new self($name, $value);
    }

    /**
     * Convert the {@see Attribute} to its string representation
     *
     * @return string
     */
    public function __toString(): string
    {
        $name = self::escape($this->name);
        $value = self::escape($this->value);

        return "${name}=${value}";
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Normalize a string according to DN rules
     *
     * @param string $string
     * @return string
     */
    private static function normalize(string $string): string
    {
        // Remove unescaped spaces at the beginning
        $string = ltrim($string, ' ');
        // Remove unescaped spaces at the end
        $string = preg_replace('#((?<!\\\\) )+$#', '', $string);

        return $string;
    }

    /**
     * Unescape a string according to DN rules
     *
     * @param string $string
     * @return string
     */
    private static function unescape(string $string): string
    {
        $chars = "\"\\\\=,+';<>";

        // Unescape special characters
        $string = preg_replace("#\\\\([$chars])#", '$1', $string);
        // Unescape `#` only at the beginning of the string
        $string = preg_replace('/^\\\\#/', '#', $string);

        return $string;
    }

    /**
     * Escape a string according to DN rules
     *
     * @param $string
     * @return string
     */
    private static function escape($string): string
    {
        $chars = "\"\\\\=,+';<>";

        // Escape $chars with backslash
        $string = preg_replace("#([${chars}])#", '\\\\$0', $string);
        // Escape hash or space at the beginning of the string with backslash
        $string = preg_replace('/^([ #])/', '\\\\$0', $string);
        // Escape space at the end of the string with backslash
        $string = preg_replace('/ $/', '\\\\ ', $string);

        return $string;
    }
}
