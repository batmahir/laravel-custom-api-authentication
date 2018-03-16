<?php namespace Batmahir\APIAuthentication;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;

class APIAuthenticationDriver implements Guard ,StatefulGuard
{

    protected $user_provider;
    protected $app;
    protected $name;

    public function __construct(UserProvider $user_provider, $app, $name)
    {

        $this->user_provider = $user_provider;
        $this->app = $app;
        $this->name = $name;


    }

   /* public function attempt(array $credentials = [], $remember = false)
    {
        dd('fuck lah');
    }*/

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {

    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {

    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {

    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {

    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        dd('here');
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {

    }


    ##---------------------------------------------------- start for implementation of StatefulGuard Interface ---------------------------
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {

    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {

    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {

    }

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @param  bool   $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function loginUsingId($id, $remember = false)
    {

    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function onceUsingId($id)
    {

    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {

    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {

    }
    ##---------------------------------------------------- end for implementation of StatefulGuard Interface ---------------------------
}