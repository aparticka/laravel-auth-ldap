<?php namespace LaravelAuthLdap;

use Illuminate\Hashing\BcryptHasher;
use LaravelAuthLdap\Contracts\LdapServer;
use Trans\Models\User;

class noLDAPConnection implements LdapServer {

    private $hasher;

    public function __construct(){

        $this->hasher = new BcryptHasher;
    }

    /**
     * Retrieve the user from the LDAP server via their username.
     *
     * @param  string $username
     * @return null
     */
    public function retrieveByUsername($username)
    {
        return null;
    }

    /**
     * Authenticate a user with a username/password combination.
     *
     * @param  string $username
     * @param  string $password
     * @return bool
     */
    public function authenticate($username, $password)
    {
        $model = app()->config['auth.model'];

        return $this->hasher->check(
            $password,
            $model::whereEmail($username)->first()->getAuthPassword()
        );
    }
}