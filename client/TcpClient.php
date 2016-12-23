<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21
 * Time: 16:10
 */

namespace com\yele\client;


use com\yele\config\Config;

class TcpClient
{
    private $conf;
    private $client;


    public function __construct()
    {
        $this->conf = new Config();
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);

        $this->client->on('start',funtion($cli){
            echo "Client Start ...\n";
        });

        $this->client->on('connect',funtion($cli){
            $this->client->send("Hello,I'm here!");
        });

        $this->client->on('receive',funtion($cli,$data){
            //TODO
            echo "you got your data:".$data;
        });

        $this->client->on('',funtion($cli){
            echo "Client Closed ...\n";
        });
    }

    public function init(){
        $this->client->connect($this->conf->host,$this->conf->port,$this->conf->timeout);
    }
}

$client = new TcpClient();
$client->init();