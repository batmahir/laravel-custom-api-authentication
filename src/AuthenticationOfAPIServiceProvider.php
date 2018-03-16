<?php

namespace Batmahir\APIAuthentication;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthenticationOfAPIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * This will simply register api-authentication-driver driver for authentication
         */
        Auth::extend('api-authentication-driver', function ($app, $name, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...

            return new APIAuthenticationDriver(Auth::createUserProvider($config['provider']),$app,$name);
            // $config['provider'] is value for the provider of web inside config/auth.php setting
        });

        Auth::provider('api-authentication-provider', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new APIAuthenticationUserProvider($app->make(APIAuthenticationConnection::class));
        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }


}
