<?php

use LaravelAuthLdap\AdLDAPLdapServer;

class AdLDAPLdapServerTest extends TestCase {

    protected $adLDAP;

    protected $adLDAPLdapServer;

    public function setUp()
    {
        parent::setUp();

        $this->adLDAPLdapServer = new AdLDAPLdapServer();
        $this->adLDAP = Mockery::mock();
        $this->adLDAPLdapServer->setAdServer($this->adLDAP);
    }

    public function testRetrieveByUsername()
    {
        $username = 'foo';

        // set up mocks
        $adLDAPUsers = Mockery::mock();
        $this->adLDAP->shouldReceive('user')->andReturn($adLDAPUsers);

        // should be null if no user was found
        $adLDAPUsers->shouldReceive('infoCollection')->once()->andReturn(false);
        $this->assertNull($this->adLDAPLdapServer->retrieveByUsername($username));

        // should return an instance of LdapUser if the user was found
        $adLDAPUsers->shouldReceive('infoCollection')->once()->andReturn(true);
        $this->assertInstanceOf('LaravelAuthLdap\Contracts\LdapUser', $this->adLDAPLdapServer->retrieveByUsername($username));
    }

    public function testAuthenticate()
    {
        $username = 'foo';
        $validPassword = 'password';
        $invalidPassword = 'invalid';

        // set up expectations
        $this->adLDAP->shouldReceive('authenticate')->with($username, $validPassword)->andReturn(true);
        $this->adLDAP->shouldReceive('authenticate')->with($username, $invalidPassword)->andReturn(false);

        $this->assertTrue($this->adLDAPLdapServer->authenticate($username, $validPassword));
        $this->assertFalse($this->adLDAPLdapServer->authenticate($username, $invalidPassword));
    }

}