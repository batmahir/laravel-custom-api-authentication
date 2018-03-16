<?php namespace Batmahir\APIAuthentication;


class APIAuthenticationConnection
{

    protected $client_secret;

    protected $client_id;

    public function __construct()
    {
        $this->client_id = env('MAIN_USER_APPLICATION_CLIENT_ID');
        $this->client_secret = env('MAIN_USER_APPLICATION_CLIENT_SECRET');
    }




}