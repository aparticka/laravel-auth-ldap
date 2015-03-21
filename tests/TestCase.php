<?php

abstract class TestCase extends \Orchestra\Testbench\TestCase {

    protected function getPackageProviders($app)
    {
        return [
            'LaravelAuthLdap\AuthLdapServiceProvider'
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

}