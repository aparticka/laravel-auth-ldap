<?php namespace LaravelAuthLdap;

use adLDAP\adLDAP;
use adLDAP\adLDAPException;
use Illuminate\Support\ServiceProvider;

class AuthLdapServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->app['auth']->extend('ldap', function($app)
        {
            return $app->make('LaravelAuthLdap\Contracts\LdapUserProvider');
        });
    }

    public function register()
    {
        $this->publishConfigs();
        $this->registerLdapInterfaces();
    }

    /**
     * Publish the config files used in the library.
     */
    public function publishConfigs()
    {
        $configFiles = [];

        foreach (['auth-ldap.php', 'adldap.php'] as $configFile)
        {
            $configFiles[__DIR__ . '/../config/' . $configFile] = config_path($configFile);
        }

        $this->publishes($configFiles, 'config');
    }

    /**
     * Bind the interfaces to their implementations in the service container.
     */
    public function registerLdapInterfaces()
    {
        $this->app->bind('LaravelAuthLdap\Contracts\LdapServer', function($app)
        {
            try{
                $server = new AdLDAPLdapServer;
                $server->setAdServer(new adLDAP($app->config['adldap']));
            } catch (adLDAPException $e){
                $server = new noLDAPConnection();
            }

            return $server;
        });

        $this->app->bind('LaravelAuthLdap\Contracts\LdapUser', function($app)
        {
            $user = new BaseLdapUser;
            $user->setConvertFields($app->config['auth-ldap.convert_fields']);
            $user->setUsernameField($app->config['auth-ldap.username_field']);

            return $user;
        });

        $this->app->bind('LaravelAuthLdap\Contracts\LdapUserProvider', function($app)
        {
            $provider = new BaseLdapUserProvider;

            $driverName = array_get($app->config['auth-ldap'], 'provider.driver');

            if ($driverName !== null)
            {
                $driver = $this->app['auth']->driver($driverName);

                $provider->setProvider($driver->getProvider());
            }

            $provider->setLdapServer($app->make('LaravelAuthLdap\Contracts\LdapServer'));

            $mustExist = array_get($app->config['auth-ldap'], 'provider.must_exist');
            $provider->setUserMustExistInProvider($mustExist === null ? false : $mustExist);

            $provider->setCredentialsFields($app->config['auth-ldap.credentials_fields']);

            return $provider;
        });
    }

}