<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22
 * Time: 16:13
 */

class TaskClient
{
    private $client;
    public function __construct() {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
    }
    public function connect() {
        if( !$this->client->connect("127.0.0.1", 9501 , 1) ) {
            echo "Connect Error";
        }
        $data = array(
            "url" =>  "http://192.168.10.19/send_mail" ,
            "param" => array(
                "username"=>'test',
                "password" => 'test'
            )
        );
        $json_data = json_encode($data);
        $this->client->send( $json_data );
    }
}

$client = new Client();
$client->connect();