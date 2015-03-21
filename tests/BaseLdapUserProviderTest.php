<?php

use LaravelAuthLdap\BaseLdapUserProvider;

class BaseLdapUserProviderTest extends TestCase {

    /**
     * @var BaseLdapUserProvider
     */
    protected $baseLdapUserProvider;

    public function setUp()
    {
        parent::setUp();

        $this->baseLdapUserProvider = new BaseLdapUserProvider;
    }

    public function testIsUsingProvider()
    {
        // should not be using provider before setting it
        $this->assertFalse($this->baseLdapUserProvider->isUsingProvider());

        $provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->baseLdapUserProvider->setProvider($provider);

        // after setting provider, should show that one is being used
        $this->assertTrue($this->baseLdapUserProvider->isUsingProvider());
    }

    public function testGetCredentialsField()
    {
        $usernameField = 'foo';
        $passwordField = 'bar';

        $credentialsFields = [
            'username' => $usernameField,
            'password' => $passwordField
        ];

        $this->baseLdapUserProvider->setCredentialsFields($credentialsFields);

        $this->assertEquals($usernameField, $this->baseLdapUserProvider->getCredentialsField('username'));
        $this->assertEquals($passwordField, $this->baseLdapUserProvider->getCredentialsField('password'));
    }

    public function testRetrieveByCredentials()
    {
        $credentialsFields = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $this->baseLdapUserProvider->setCredentialsFields($credentialsFields);

        $ldapServer = Mockery::mock('LaravelAuthLdap\Contracts\LdapServer');
        $this->baseLdapUserProvider->setLdapServer($ldapServer);

        $ldapUser = Mockery::mock('LaravelAuthLdap\Contracts\LdapUser');
        $ldapServer->shouldReceive('retrieveByUsername')->andReturn($ldapUser);

        $credentials = [
            'user' => 'foobar',
            'pass' => 'foopass'
        ];

        // if not using provider, should retrieve from LDAP server
        $this->assertEquals($ldapUser, $this->baseLdapUserProvider->retrieveByCredentials($credentials));

        // now using provider
        $provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->baseLdapUserProvider->setProvider($provider);

        $provider->shouldReceive('retrieveByCredentials')->times(2)->andReturn(null);

        // if not found in provider and doesn't matter if it exists, should default back to LDAP
        $this->baseLdapUserProvider->setUserMustExistInProvider(false);
        $this->assertEquals($ldapUser, $this->baseLdapUserProvider->retrieveByCredentials($credentials));

        // if not found in provider and must exist
        $this->baseLdapUserProvider->setUserMustExistInProvider(true);
        $this->assertNull($this->baseLdapUserProvider->retrieveByCredentials($credentials));

        $providerUser = Mockery::mock('Illuminate\Contracts\Auth\Authenticatable');
        $provider->shouldReceive('retrieveByCredentials')->times(2)->andReturn($providerUser);

        // if found in provider, should always return from the provider
        $this->assertEquals($providerUser, $this->baseLdapUserProvider->retrieveByCredentials($credentials));
        $this->baseLdapUserProvider->setUserMustExistInProvider(false);
        $this->assertEquals($providerUser, $this->baseLdapUserProvider->retrieveByCredentials($credentials));
    }

    public function testValidateCredentials()
    {
        $credentialsFields = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $this->baseLdapUserProvider->setCredentialsFields($credentialsFields);

        $ldapServer = Mockery::mock('LaravelAuthLdap\Contracts\LdapServer');
        $this->baseLdapUserProvider->setLdapServer($ldapServer);
        $ldapServer->shouldReceive('authenticate')->once()->andReturn(true);

        $credentials = ['user' => 'foo', 'pass' => 'bar'];
        $user = Mockery::mock('Illuminate\Contracts\Auth\Authenticatable');

        $this->assertTrue($this->baseLdapUserProvider->validateCredentials($user, $credentials));
    }

    public function testRetrieveById()
    {
        $credentialsFields = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $this->baseLdapUserProvider->setCredentialsFields($credentialsFields);

        $ldapServer = Mockery::mock('LaravelAuthLdap\Contracts\LdapServer');
        $this->baseLdapUserProvider->setLdapServer($ldapServer);

        $ldapUser = Mockery::mock('LaravelAuthLdap\Contracts\LdapUser');
        $ldapServer->shouldReceive('retrieveByUsername')->andReturn($ldapUser);

        $id = 'foo';

        // if not using provider, should retrieve from LDAP server
        $this->assertEquals($ldapUser, $this->baseLdapUserProvider->retrieveById($id));

        // now using provider
        $provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->baseLdapUserProvider->setProvider($provider);

        $provider->shouldReceive('retrieveById')->times(2)->andReturn(null);

        // if not found in provider and doesn't matter if it exists, should default back to LDAP
        $this->baseLdapUserProvider->setUserMustExistInProvider(false);
        $this->assertEquals($ldapUser, $this->baseLdapUserProvider->retrieveById($id));

        // if not found in provider and must exist
        $this->baseLdapUserProvider->setUserMustExistInProvider(true);
        $this->assertNull($this->baseLdapUserProvider->retrieveById($id));

        $providerUser = Mockery::mock('Illuminate\Contracts\Auth\Authenticatable');
        $provider->shouldReceive('retrieveById')->times(2)->andReturn($providerUser);

        // if found in provider, should always return from the provider
        $this->assertEquals($providerUser, $this->baseLdapUserProvider->retrieveById($id));
        $this->baseLdapUserProvider->setUserMustExistInProvider(false);
        $this->assertEquals($providerUser, $this->baseLdapUserProvider->retrieveById($id));
    }

    public function testRetrieveByToken()
    {
        // not using provider, shouldn't return anything
        $this->assertNull($this->baseLdapUserProvider->retrieveByToken('id', 'token'));

        $provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->baseLdapUserProvider->setProvider($provider);

        $providerUser = Mockery::mock('Illuminate\Contracts\Auth\Authenticatable');
        $provider->shouldReceive('retrieveByToken')->andReturn($providerUser);

        // using provider, should return from it
        $this->assertEquals($providerUser, $this->baseLdapUserProvider->retrieveByToken('id', 'token'));
    }

    public function testUpdateRememberToken()
    {
        $provider = Mockery::mock('Illuminate\Contracts\Auth\UserProvider');
        $this->baseLdapUserProvider->setProvider($provider);

        $ldapUser = Mockery::mock('LaravelAuthLdap\Contracts\LdapUser');
        $provider->shouldReceive('updateRememberToken')->never();

        // if not using provider, don't update token
        $this->baseLdapUserProvider->updateRememberToken($ldapUser, 'token');

        $providerUser = Mockery::mock('Illuminate\Contracts\Auth\Authenticatable');
        $provider->shouldReceive('updateRememberToken')->once();

        // if using provider, should update token in provider
        $this->baseLdapUserProvider->updateRememberToken($providerUser, 'token');
    }

}
