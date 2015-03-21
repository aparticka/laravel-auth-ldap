<?php namespace LaravelAuthLdap\Contracts; 

interface LdapServer {

    /**
     * Retrieve the user from the LDAP server via their username.
     *
     * @param  string $username
     * @return LdapUserInterface|null
     */
    public function retrieveByUsername($username);

    /**
     * Authenticate a user with a username/password combination.
     *
     * @param  string $username
     * @param  string $password
     * @return bool
     */
    public function authenticate($username, $password);

}