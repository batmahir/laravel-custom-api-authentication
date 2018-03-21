<?php namespace Batmahir\APIAuthentication;


class APIAuthenticationConnection
{

    protected $client_secret;

    protected $client_id;

    protected $main_app;

    public function __construct()
    {
        $this->client_id = env('MAIN_USER_APPLICATION_CLIENT_ID');
        $this->client_secret = env('MAIN_USER_APPLICATION_CLIENT_SECRET');
        $this->main_app = env('MAIN_USER_APPLICATION');
    }

    public function getConnection()
    {
        $connection = \json_decode(\json_encode(get_object_vars($this)));

        return $connection;
    }




}