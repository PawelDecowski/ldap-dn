# LDAP DN

LDAP Distinguished Name parsing and manipulation library for PHP.

## Table of contents

* [Features](#features)
* [Installation](#installation)
* [How to use](#how-to-use)
  * [Parsing DNs and accessing their components](#parsing-dns-and-accessing-their-components)
    * [Create a DN object from a string](#create-a-dn-object-from-a-string)
    * [Back to string representation](#back-to-string-representation)
    * [Access RDNs by index](#access-rdns-by-index)
    * [Iterate RDNs](#iterate-rdns)
    * [Access attributes](#access-attributes)
    * [Multivalued RDNs](#multivalued-rdns)
    * [Special characters](#special-characters)
    * [Filter by attribute name](#filter-by-attribute-name)
    * [Get the parent DN](#get-the-parent-dn)
  * [Manipulating DNs](#manipulating-dns)
    * [Remove a fragment of a DN](#remove-a-fragment-of-a-dn)
  * [Constructing DNs](#constructing-dns)

## Features

* access individual RDNs by index
* iterate RDNs
* filter by attribute name
* support for escaping special characters
* access attribute values
* remove fragments of a DN
* construct DNs
* case-insensitive but case-preserving (lookups are case-insensitive but attribute names’ and values’ case is preserved)

## Installation

```shell
$ composer require paweldecowski/ldap-dn
```

## How to use

### Parsing DNs and accessing their components

#### Create a DN object from a string

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');
```

#### Back to string representation

`Dn` class as well as classes representing its components (`Rdn`, `Attribute`) implement the `__toString` method.
It means that in string context, they automatically become strings:

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn; // 'cn=john.doe,ou=it,ou=leadership,dc=example,dc=org'
echo $dn[0]; // 'dc=org'
echo $dn->filter('ou'); // 'ou=it,ou=leadership'
```

#### Access RDNs by index

Note that `Rdn`s in a `Dn` object are reversed in relation to the DN string (if read from left to right).
In a `Dn` with `n` `Rdn`s, the right-most `Rdn` is at index `0` and the left-most `Rdn` is at index `n-1`.
This is because it’s more natural and common to have the root object at index `0`.

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn[0]; // 'dc=org'
echo $dn[4]; // 'cn=john.doe'
```

#### Iterate RDNs

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

foreach ($dn as $rdn) {
    echo $rdn, "\n";
}
```

outputs:

```
dc=org
dc=example
ou=leadership
ou=it
cn=john.doe
```

#### Access attributes

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn[0]['dc']->getValue(); // 'org'
echo $dn[4]['cn']->getValue(); // 'john.doe'
```

If there’s only one instance of a certain attribute, you can get its value directly from the `Dn` object:

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn->getValue('cn'); // 'john.doe'
```

If mulitiple attributes with the specified name are found, a `MultipleAttributesReturnedException` is thrown.

#### Multivalued RDNs

`Rdn`s are allowed to have multiple attributes, separated by the `+` character. You can access them using array
dereferencing syntax.

```php
$dn = LdapDn\Dn::fromString('cn=john.doe+uid=123,ou=it,ou=leadership,dc=example,dc=org');

echo $dn[4]; // 'cn=john.doe+uid=123'
echo $dn[4]['cn']; // 'cn=john.doe'
echo $dn[4]['cn']->getValue(); // 'john.doe'
echo $dn[4]['uid']; // 'uid=123'
echo $dn[4]['uid']->getValue(); // '123' 
```

You can also iterate attributes if you don’t know their names.

```php
$dn = LdapDn\Dn::fromString('cn=john.doe+uid=123,ou=it,ou=leadership,dc=example,dc=org');

foreach ($dn[4] as $attribute) {
    echo $attribute->getName(), ' is ', $attribute->getValue(), "\n";
}
```

outputs:

```
cn is john.doe
uid is 123
```

#### Special characters

```php
$dn = LdapDn\Dn::fromString('cn=doe\, john,ou=it,ou=leadership,dc=example,dc=org');

echo $dn[4]; // 'cn=doe\, john'
echo $dn[4]['cn']->getValue(); // 'doe, john'
```

#### Filter by attribute name

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn->filter('ou'); // 'ou=it,ou=leadership'
```

Note that even though the result of `filter()` is a `Dn` object, it may not be a valid Distinguished Name (for example if you remove the root RDN).
This library doesn’t have the knowledge of your LDAP structure, so it can’t ensure validity.

#### Get the parent DN

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');

echo $dn->getParent(); // 'ou=it,ou=leadership,dc=example,dc=org'
echo $dn->getParent()->getParent(); // 'ou=leadership,dc=example,dc=org'
```

### Manipulating DNs

`Dn`, `Rdn` and `Attribute` are immutable so all manipulation functions return a new object.

#### Remove a fragment of a DN

Sometimes you may want to remove a fragment of a DN, for example its base DN.

```php
$dn = LdapDn\Dn::fromString('cn=john.doe,ou=it,ou=leadership,dc=example,dc=org');
$fragmentToRemove = LdapDn\Dn::fromString('dc=example,dc=org');

echo $dn->withRemoved($fragmentToRemove); // 'cn=john.doe,ou=it,ou=leadership'
```

### Constructing DNs

While the main purpose of the library is parsing Dns, you can also construct them.

```php
use LdapDn\Dn;
use LdapDn\Rdn;
use LdapDn\Attribute;

$dn = new Dn([
    new Rdn([new Attribute('dc', 'org')]),
    new Rdn([new Attribute('dc', 'example')]),
    new Rdn([new Attribute('ou', 'leadership')]),
    new Rdn([new Attribute('ou', 'it')]),
    new Rdn([new Attribute('cn', 'doe, john'), new Attribute('uid', '123')]),
]);

echo $dn; // 'cn=doe\, john+uid=123,ou=it,ou=leadership,dc=example,dc=org'
```

Most Rdns contain a single attribute, so you can construct them with a shortcut.

```php
use LdapDn\Dn;
use LdapDn\Rdn;
use LdapDn\Attribute;

$dn = new Dn([
    Rdn::withNameAndValue('dc', 'org'),
    Rdn::withNameAndValue('dc', 'example'),
    Rdn::withNameAndValue('ou', 'leadership'),
    Rdn::withNameAndValue('ou', 'it'),
    new Rdn([new Attribute('cn', 'doe, john'), new Attribute('uid', '123')]),
]);

echo $dn; // 'cn=doe\, john+uid=123,ou=it,ou=leadership,dc=example,dc=org'
```

## Exceptions

### AttributeNotFoundException

Thrown if an attribute is not found in an `Dn`.

### DnNotFoundException

Thrown if an `Dn` cannot be found in another `Dn`

### InvalidAttributeStringException

Thrown if a string representing an attribute is malformed.

### MultipleAttributesReturned exception

Thrown when multiple `Attribute`s are returned when exactly 1 was expected.

### NotImplementedException

Thrown when an unimplemented method is called.