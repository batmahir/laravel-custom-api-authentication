<?php namespace Batmahir\APIAuthentication;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\MessageBag;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Auth\AuthenticationException;

class APIAuthenticationUserProvider implements UserProvider , Authenticatable
{

    protected $connection;

    protected $user;

    public function __construct(APIAuthenticationConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getAPIAuthenticationConnection()
    {
        return $this->connection->getConnection();
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $data =
            Curl::to($this->getAPIAuthenticationConnection()->main_app.'/oauth2-api/v1/user')
                ->asJson()
                ->withHeaders( array( 'Authorization: '.$identifier) )
                ->get();

        return $data;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {

        if (empty($credentials))
        {
            return ;
        }

        $http =
        Curl::to($this->getAPIAuthenticationConnection()->main_app.'/oauth2-api/v1/direct-authorize')
        ->withData([
                    'grant_type' => 'password',
                    'client_id' => $this->getAPIAuthenticationConnection()->client_id,
                    'client_secret' => $this->getAPIAuthenticationConnection()->client_secret,
                    'username' => $credentials['email'],
                    'password' => $credentials['password'],
                    'scope' => '*'
        ])->asJson()
        ->post();

        if(isset($http->error))
        {
            $errors  = new MessageBag;
            $errors->add('login error',$http->error);
            return ;

        }

        $http2 =
        Curl::to($this->getAPIAuthenticationConnection()->main_app.'/oauth2-api/v1/user')
            ->asJson()
            ->withHeaders( array( 'Authorization: Bearer '.$http->access_token) )
            ->get();

        $http = \json_decode(\json_encode($http),true);
        $http2 = \json_decode(\json_encode($http2),true);
        $user = \json_decode(\json_encode(array_merge($http2,$http)));
        $this->setUserAttribute($user) ;

        return $this;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $data = $this->retrieveByCredentials($credentials);
        if(!isset($data ) || !isset($user)) // if either one is not set , return false
            return false;

        return true;
    }


    #---------------------------------------------------------------------- start Authenticatable contract interface ----------------------------------------------

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {

        return 'login_api_authentication_'.sha1(static::class);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        if (\session()->has($this->getAuthIdentifierName()))
        {
            return \session()->get($this->getAuthIdentifierName());
        }

        return null;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        throw new AuthenticationException('This method is unsupported for this driver');
    }
    #---------------------------------------------------------------------- end Authenticatable contract interface ----------------------------------------------

    public function getUser()
    {
        return $this->user;
    }

    public function setUserAttribute($user_data)
    {
        $this->user = $user_data;

        foreach ($user_data as $key => $value)
        {
            $this->createProperty($key,$value);
        }

        if(isset($this->access_token))
        {
            $this->access_token = 'Bearer '.$this->access_token;
        }


    }

    public function createProperty($name, $value){
        $this->{$name} = $value;
    }


    public function destroyAuthIdentifierSession()
    {
        if (\session()->has($this->getAuthIdentifierName())) {
            \session()->forget($this->getAuthIdentifierName());
        }
    }

}