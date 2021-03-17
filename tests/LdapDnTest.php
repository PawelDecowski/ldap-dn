<?php

use LdapDn\Dn;
use LdapDn\Rdn;
use PHPUnit\Framework\TestCase;

class LdapDnTest extends TestCase
{
    private $rdnStrings = [
        'dc=org',
        'dc=example',
        'ou=leadership',
        'ou=it',
        'cn=doe\, john+uid=123',
    ];

    /**
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Dn::offsetExists
     * @covers LdapDn\Dn::count
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::count
     * @covers LdapDn\split_on_unescaped
     */
    public function testParsesDn(): Dn
    {
        $dnString = join(',', array_reverse($this->rdnStrings));
        $dn = Dn::fromString($dnString);

        $this->assertTrue(isset($dn[0]));
        $this->assertFalse(isset($dn[5]));
        $this->assertCount(5, $dn);
        $this->assertCount(1, $dn[0]);
        $this->assertCount(2, $dn[4]);
        $this->assertEquals('dc=org', (string)$dn[0]);
        $this->assertEquals('dc=example', (string)$dn[1]);
        $this->assertEquals('ou=leadership', (string)$dn[2]);
        $this->assertEquals('ou=it', (string)$dn[3]);
        $this->assertEquals('cn=doe\, john+uid=123', (string)$dn[4]);

        return $dn;
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Dn::offsetExists
     * @covers LdapDn\Dn::current
     * @covers LdapDn\Dn::key
     * @covers LdapDn\Dn::next
     * @covers LdapDn\Dn::rewind
     * @covers LdapDn\Dn::valid
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     */
    public function testDnIterator(Dn $dn)
    {
        foreach ($dn as $index => $rdn) {
            $this->assertEquals($this->rdnStrings[$index], (string)$rdn);
        }
    }

    /**
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Exceptions\InvalidAttributeStringException::forString
     */
    public function testInvalidAttributeStringThrows()
    {
        // 'ou' is an invalid attribute string because it isn't in the form `name=value`
        $dnString = 'cn=john,ou';

        $this->expectException('LdapDn\Exceptions\InvalidAttributeStringException');

        Dn::fromString($dnString);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::__toString
     * @covers LdapDn\Dn::filter
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::offsetExists
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\split_on_unescaped
     */
    public function testFilter(Dn $dn)
    {
        $this->assertEquals('cn=doe\, john', (string)$dn->filter('cn'));
        $this->assertEquals('ou=it,ou=leadership', (string)$dn->filter('ou'));
        $this->assertEquals('dc=example,dc=org', (string)$dn->filter('dc'));
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::filter
     * @covers LdapDn\Dn::count
     * @covers LdapDn\Dn::getValue
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::offsetExists
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     */
    public function testDnGetValue(Dn $dn)
    {
        $this->assertEquals('doe, john', $dn->getValue('cn'));
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::filter
     * @covers LdapDn\Dn::count
     * @covers LdapDn\Dn::getValue
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::offsetExists
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     * @covers LdapDn\Exceptions\MultipleAttributesReturnedException::forName
     */
    public function testDnGetValueThrowsIfMoreThanOneAttributeWithThisName(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\MultipleAttributesReturnedException');

        $dn->getValue('dc');
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::filter
     * @covers LdapDn\Dn::count
     * @covers LdapDn\Dn::getValue
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::offsetExists
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     * @covers LdapDn\Exceptions\AttributeNotFoundException::forName
     */
    public function testDnGetValueThrowsIfAttributeNotFound(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\AttributeNotFoundException');

        $dn->getValue('non_existent_attribute');
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::__toString
     * @covers LdapDn\Dn::getRDNIndex
     * @covers LdapDn\Dn::withRemoved
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     */
    public function testRemoveFragment(Dn $dn)
    {
        $removeDnString = 'dc=example,dc=org';

        $dnFragment = Dn::fromString($removeDnString);

        $dn = $dn->withRemoved($dnFragment);

        $this->assertEquals('cn=doe\, john+uid=123,ou=it,ou=leadership', (string)$dn);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::__toString
     * @covers LdapDn\Dn::getRDNIndex
     * @covers LdapDn\Dn::withRemoved
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     * @covers LdapDn\Exceptions\DnNotFoundException::forDn
     */
    public function testRemoveFragmentDoesntExist(Dn $dn)
    {
        $dnFragment = Dn::fromString('dc=example,dc=should_be_org');

        $this->expectException('LdapDn\Exceptions\DnNotFoundException');

        $dn->withRemoved($dnFragment);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::__toString
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::getParent
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\split_on_unescaped
     */
    public function testGetParent(Dn $dn)
    {
        $parent = $dn->getParent();

        $this->assertEquals('ou=it,ou=leadership,dc=example,dc=org', (string)$parent);
        $this->assertEquals('cn=doe\, john+uid=123,ou=it,ou=leadership,dc=example,dc=org', (string)$dn);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetSet
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     */
    public function testDnSetIsNotImplemented(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\NotImplementedException');

        $dn[0] = new LdapDn\Rdn([]);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetSet
     * @covers LdapDn\Dn::offsetUnset
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\split_on_unescaped
     */
    public function testDnUnsetIsNotImplemented(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\NotImplementedException');

        unset($dn[0]);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::offsetSet
     * @covers LdapDn\split_on_unescaped
     */
    public function testRdnSetIsNotImplemented(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\NotImplementedException');

        $dn[0][0] = new LdapDn\Attribute('test', 'test');
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Dn::offsetUnset
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::offsetUnset
     * @covers LdapDn\split_on_unescaped
     */
    public function testRdnUnsetIsNotImplemented(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\NotImplementedException');

        unset($dn[0][0]);
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Dn::offsetUnset
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::offsetUnset
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\split_on_unescaped
     */
    public function testGetAttributeValue(Dn $dn)
    {
        $this->assertEquals('org', $dn[0]['dc']->getValue());
        $this->assertEquals('example', $dn[1]['dc']->getValue());
        $this->assertEquals('doe, john', $dn[4]['cn']->getValue());
        $this->assertEquals('123', $dn[4]['uid']->getValue());
    }

    /**
     * @depends testParsesDn
     *
     * @covers LdapDn\Dn::__construct
     * @covers LdapDn\Dn::fromString
     * @covers LdapDn\Dn::offsetGet
     * @covers LdapDn\Dn::offsetUnset
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::offsetUnset
     * @covers LdapDn\Rdn::offsetGet
     * @covers LdapDn\split_on_unescaped
     * @covers LdapDn\Exceptions\AttributeNotFoundException::forName
     */
    public function testGetAttributeFailsForNonExistingName(Dn $dn)
    {
        $this->expectException('LdapDn\Exceptions\AttributeNotFoundException');

        $dn[0]['bad_attribute_name'];
    }

    /**
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::__toString
     * @covers LdapDn\Attribute::escape
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers LdapDn\Rdn::__toString
     * @covers LdapDn\Rdn::withNameAndValue
     */
    public function testCreateRdnWithNameAndValue()
    {
        $rdn = Rdn::withNameAndValue('cn', 'doe, john');

        self::assertEquals('cn=doe\, john', (string)$rdn);
    }

    /**
     * @covers LdapDn\Attribute::__construct
     * @covers LdapDn\Attribute::fromString
     * @covers LdapDn\Attribute::getName
     * @covers LdapDn\Attribute::getValue
     * @covers LdapDn\Attribute::normalize
     * @covers LdapDn\Attribute::unescape
     * @covers LdapDn\Rdn::__construct
     * @covers \LdapDn\Rdn::fromString
     * @covers LdapDn\Rdn::current
     * @covers LdapDn\Rdn::key
     * @covers LdapDn\Rdn::next
     * @covers LdapDn\Rdn::rewind
     * @covers LdapDn\Rdn::valid
     * @covers LdapDn\split_on_unescaped
     */
    public function testAttributeIterator()
    {
        $rdn = Rdn::fromString('cn=john.doe+uid=123');

        $attributes = [
            [
                'name' => 'cn',
                'value' => 'john.doe'
            ],
            [
                'name' => 'uid',
                'value' => '123'
            ]
        ];

        foreach($rdn as $i => $attribute) {
            $this->assertEquals($attributes[$i]['name'], $attribute->getName());
            $this->assertEquals($attributes[$i]['value'], $attribute->getValue());
        }
    }
}
