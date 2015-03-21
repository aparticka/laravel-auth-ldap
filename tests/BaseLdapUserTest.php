<?php

use LaravelAuthLdap\BaseLdapUser;

class BaseLdapUserTest extends TestCase {

    /**
     * @var BaseLdapUser
     */
    protected $baseLdapUser;

    public function setUp()
    {
        parent::setUp();

        $this->baseLdapUser = new BaseLdapUser;
    }

    public function testConvertFieldsMagicMethod()
    {
        $displayName = 'foo';
        $otherField = 500;

        $user = new stdClass;
        $user->displayname = $displayName;
        $user->otherField = $otherField;

        $this->baseLdapUser->setUser($user);
        $this->baseLdapUser->setConvertFields(['name' => 'displayname']);

        // check that the magic method converted the property name correctly
        $this->assertEquals($displayName, $this->baseLdapUser->name);

        // check that the magic method also pulls without converting
        $this->assertEquals($displayName, $this->baseLdapUser->displayname);

        // check that the magic method grabbed the field from the user info
        $this->assertEquals($otherField, $this->baseLdapUser->otherField);
    }

    public function testGetAuthIdentifier()
    {
        $usernameField = 'username';
        $username = 'foobar';

        $user = new stdClass;
        $user->$usernameField = $username;

        $this->baseLdapUser->setUser($user);
        $this->baseLdapUser->setUsernameField($usernameField);

        // check that the identifier is being pulled correctly
        $this->assertEquals($username, $this->baseLdapUser->getAuthIdentifier());
    }

}