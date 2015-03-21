<?php namespace LaravelAuthLdap;

use LaravelAuthLdap\Contracts\LdapUser;

class BaseLdapUser implements LdapUser {

    /**
     * An array of fields with names to be converted for the magic method __get().
     *
     * @var array
     */
    protected $convertFields;

    /**
     * The object returned from LDAP with user information.
     *
     * @var object
     */
    protected $user;

    /**
     * The name of the LDAP field that the username is stored.
     *
     * @var string
     */
    protected $usernameField;

    /**
     * Set the fields to convert.
     *
     * @param $convertFields
     */
    public function setConvertFields($convertFields)
    {
        $this->convertFields = $convertFields;
    }

    /**
     * Set the username field name.
     *
     * @param $usernameField
     */
    public function setUsernameField($usernameField)
    {
        $this->usernameField = $usernameField;
    }

    /**
     * Set the user information.
     *
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Use the magic method to get to properties on the user object.
     *
     * @param  string $field
     * @return mixed
     */
    public function __get($field)
    {
        if (isset($this->convertFields[$field]))
        {
            $field = $this->convertFields[$field];
        }

        if (isset($this->user->$field))
        {
            return $this->user->$field;
        }
    }

    public function getAuthIdentifier()
    {
        $usernameField = $this->usernameField;
        return $this->user->$usernameField;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
        return null;
    }

}