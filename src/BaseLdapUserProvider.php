<?php namespace LaravelAuthLdap;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use LaravelAuthLdap\Contracts\LdapServer;
use LaravelAuthLdap\Contracts\LdapUser;
use LaravelAuthLdap\Contracts\LdapUserProvider;

class BaseLdapUserProvider implements LdapUserProvider {

    /**
     * The names of the fields used when credentials are passed.
     *
     * @var array
     */
    protected $credentialsFields = [];

    /**
     * The LDAP server to use for queries.
     *
     * @var LdapServer
     */
    protected $ldapServer;

    /**
     * The secondary provider to use.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $provider;

    /**
     * Whether the user must exist in the secondary provider to log in or not.
     *
     * @var bool
     */
    protected $userMustExistInProvider = false;

    /**
     * Get the specified credentials field name.
     *
     * @param string $type
     * @return string
     */
    public function getCredentialsField($type)
    {
        return array_get($this->credentialsFields, $type);
    }

    /**
     * Sets the credentials fields.
     *
     * @param $credentialsFields
     */
    public function setCredentialsFields($credentialsFields)
    {
        $this->credentialsFields = $credentialsFields;
    }

    /**
     * Sets the LDAP server.
     *
     * @param LdapServer $ldapServer
     */
    public function setLdapServer(LdapServer $ldapServer)
    {
        $this->ldapServer = $ldapServer;
    }

    /**
     * Sets the provider.
     *
     * @param UserProvider $provider
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Sets whether or not a user must exist in the provider to log in.
     *
     * @param bool $mustExist
     */
    public function setUserMustExistInProvider($mustExist)
    {
        $this->userMustExistInProvider = $mustExist;
    }

    /**
     * Checks whether a secondary provider is set or not.
     *
     * @return bool
     */
    public function isUsingProvider()
    {
        return $this->provider !== null;
    }

    public function retrieveById($identifier)
    {
        // if provider is set, grab from there
        if ($this->isUsingProvider())
        {
            $user = $this->provider->retrieveById($identifier);
            if ($user !== null || $this->userMustExistInProvider) return $user;
        }

        // else grab from LDAP
        return $this->ldapServer->retrieveByUsername($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // if provider is set, grab from there
        if ($this->isUsingProvider()) return $this->provider->retrieveByToken($identifier, $token);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // if provider is set and the user passed isn't an LDAP one, update using the provider
        if ($this->isUsingProvider() && ! $user instanceof LdapUser)
        {
            $this->provider->updateRememberToken($user, $token);
        }
    }

    public function retrieveByCredentials(array $credentials)
    {
        if ($this->ldapServer instanceof noLDAPConnection && $this->isUsingProvider())
        {
            // get the user from the provider
            $user = $this->provider->retrieveByCredentials($credentials);
            if ($user !== null) {
                return $user;
            } else{
                return null;
            }
        }

        // grab the username from the credentials passed
        $username = $this->getUserName($credentials);

        if ($username !== null)
        {
            // get the user from LDAP
            $ldapUser = $this->ldapServer->retrieveByUsername($username);

            $model = app()->config['auth.model'];

            $passwordField = $this->getCredentialsField('password');

            $user =  $model::firstOrCreate([
                'name' => $ldapUser->name,
                'email' => $ldapUser->username
            ]);

            if(empty($user->password)) {
                $user->password = bcrypt(array_get($credentials, $passwordField));
                $user->save();
            }


            return $user;
        }
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // get the names of the credentials fields
        $usernameField = $this->getCredentialsField('username');
        $passwordField = $this->getCredentialsField('password');

        // validate with the LDAP server
        return $this->ldapServer->authenticate($credentials[$usernameField], $credentials[$passwordField]);
    }

    /**
     * @param array $credentials
     * @param $usernameField
     * @return array|mixed|null
     */
    private function getUserName(array $credentials)
    {
        // get the name of the credentials username field
        $usernameField = $this->getCredentialsField('username');

        $username = ($usernameField !== null) ? array_get($credentials, $usernameField) : null;
        $username = explode('@', $username);
        $username = array_shift($username);

        return $username;
    }

}
