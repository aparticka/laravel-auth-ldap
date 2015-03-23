# laravel-auth-ldap

[![Build Status](https://travis-ci.org/aparticka/laravel-auth-ldap.png)](https://travis-ci.org/aparticka/laravel-auth-ldap)

LDAP authentication driver for [Laravel 5](http://laravel.com)

## Installation

### Adding via Composer

Add to composer.json and install with `composer install`

    {
      require: {
        "aparticka/laravel-auth-ldap": "dev-master"
      }
    }

or use `composer require aparticka/laravel-auth-ldap`

### Add to Laravel

Modify your `config/app.php` file and add the service provider to the providers array.

    'LaravelAuthLdap\AuthLdapServiceProvider'

Copy the configuration files to your app.

    php artisan vendor:publish --provider="LaravelAuthLdap\AuthLdapServiceProvider"

Update your `config/auth.php` to use the `ldap` driver.

    'driver' => 'ldap'

## Configuration

There are two configuration files included, one for general options - `auth-ldap.php` and one for the included LDAP provider [adLDAP](https://github.com/adldap/adLDAP) - `adldap.php`.

### auth-ldap.php

* `provider` `array` - secondary provider to be used for auth
  * `driver` `string` - the driver to use
  * `must_exist` `bool` - if the user must exist in the provider to log in
* `convert_fields` `array` - maps dynamic properties on the `Authenticatable` user object
* `credentials_fields` `array` - the field names used for user credentials
  * `username` `string` - the authentication field name used for the username
  * `password` `string` - the authentication field name used for the password
* `username_field` `string` - the LDAP field used for the username

### adldap.php

Configuration variables used in creation of the adLDAP client. [Documentation](https://github.com/adldap/adLDAP)

## Extending

If you wish to extend any of the classes, just add your own service provider and bind your custom implementations to the provided interfaces. The provided implementations were designed to be extended so you can use them as a base to extend from if you wish.

## License

laravel-auth-ldap is distributed under the terms of the MIT license.

## About

Created by [Adam Particka (aparticka)](https://github.com/aparticka)
