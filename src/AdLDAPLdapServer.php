<?php namespace LaravelAuthLdap; 

use adLDAP\adLDAP;
use Illuminate\Contracts\Foundation\Application;
use LaravelAuthLdap\Contracts\LdapServer;

class AdLDAPLdapServer implements LdapServer {

    /**
     * The AD server to query/authenticate from.
     *
     * @var adLDAP
     */
    protected $adServer;

    /**
     * Sets the AD server.
     *
     * @param $adServer
     */
    public function setAdServer($adServer)
    {
        $this->adServer = $adServer;
    }

    public function retrieveByUsername($username)
    {
        $user = $this->adServer->user()->infoCollection($username);
        if ($user !== false)
        {
            $ldapUser = app('LaravelAuthLdap\Contracts\LdapUser');
            $ldapUser->setUser($user);

            return $ldapUser;
        }
    }

    public function authenticate($username, $password)
    {
        return $this->adServer->authenticate($username, $password);
    }
}