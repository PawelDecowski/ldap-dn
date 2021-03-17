<?php
declare(strict_types=1);

namespace LdapDn;

if (!function_exists('split_on_unescaped')) {
    /**
     * Splits the given string by specified character, only if it’s not escaped by a backslash
     *
     * @param string $char
     * @param string $string
     * @return array|false|string[]
     */
    function split_on_unescaped(string $char, string $string)
    {
        return preg_split("#(?<!\\\\)${char}#", $string);
    }
}