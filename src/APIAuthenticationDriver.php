<?php namespace Batmahir\APIAuthentication;

use App\Http\Controllers\Auth\LoginController;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Auth\AuthenticationException;

class APIAuthenticationDriver implements Guard ,StatefulGuard
{

    protected $user_provider;
    protected $app;
    protected $name;
    protected $connection;
    protected $user;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

   // protected $loginPath;

    public function __construct(UserProvider $user_provider, $app, $name)
    {

        $this->user_provider = $user_provider;
        $this->app = $app;
        $this->name = $name;
        $this->connection = $this->user_provider->getAPIAuthenticationConnection();
        //$this->loginPath = \request()->getSchemeAndHttpHost(). (new LoginController())->redirectPath();


    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate()
    {
        $this->user();

        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
         if($this->user_provider->retrieveById($this->user_provider->getAuthIdentifier()))
             return true;

         return false;
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        if($this->user_provider->retrieveById($this->user_provider->getAuthIdentifier()))
            return false;

        return true;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        if (\session()->has($this->user_provider->getAuthIdentifierName()))
        {
            $user =  \session()->get($this->user_provider->getAuthIdentifierName());
            return $user;
        }

        $authenticated_user = $this->user_provider->retrieveById($this->user_provider->getAuthIdentifier());
        if (!isset($authenticated_user))
        {
            return;
        }
        $this->user_provider->setUserAttribute($authenticated_user);
        $this->setUser($this->user_provider);

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->user->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials,false);
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        $this->loggedOut = false;

        $this->fireAuthenticatedEvent($user);

        return $this;
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
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->user_provider->retrieveByCredentials($credentials);

        if($user)
        {
            $this->login($user, $remember);

            return true;
        }
        //if($this->user_provider->validateCredentials($user,$credentials))
        //{
           // $this->login($user, $remember);
           // return true;
        //}

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        $this->fireFailedEvent($user, $credentials);
        return false;
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        throw new AuthenticationException('This method is unsupported for this driver');
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
        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);
        $this->setUser($user);
        $this->user->destroyAuthIdentifierSession();
        //\session()->put([ $this->user->getAuthIdentifierName() => $this->user->getUser()->token_type.' '.$this->user->getUser()->access_token ]);
        $this->user->access_token = $this->user->getUser()->token_type.' '.$this->user->getUser()->access_token ;
        \session()->put($this->user->getAuthIdentifierName() ,$this->user);

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
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $this->user_provider->destroyAuthIdentifierSession();

        $this->user = null;

        $this->loggedOut = true;
    }
    ##---------------------------------------------------- end for implementation of StatefulGuard Interface ---------------------------


    /**
     * Fire the attempt event with the arguments.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return void
     */
    protected function fireAttemptEvent(array $credentials, $remember = false)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new \Illuminate\Auth\Events\Attempting(
                $credentials, $remember
            ));
        }
    }

    /**
     * Fire the authenticated event if the dispatcher is set.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function fireAuthenticatedEvent($user)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new \Illuminate\Auth\Events\Authenticated($user));
        }
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @param  array  $credentials
     * @return void
     */
    protected function fireFailedEvent($user, array $credentials)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new \Illuminate\Auth\Events\Failed($user, $credentials));
        }
    }

    /**
     * Fire the login event if the dispatcher is set.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    protected function fireLoginEvent($user, $remember = false)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new \Illuminate\Auth\Events\Login($user, $remember));
        }
    }

}