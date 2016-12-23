<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/21
 * Time: 15:49
 */

namespace com\yele\server;

use com\yele\config\Config;
use Swoole;

class TcpServer
{
    private $conf;
    private $server;
    public function __construct()
    {
        $this->conf = new Config();
        $this->server = new Swoole\Server($this->conf->host, $this->conf->port);

        $this->server->set($this->conf->setting);

        $this->server->on('start',function($this->server){
            echo "Server Start ... \n";
        });

        $this->server->on('connect',function($this->server,$fd){
            echo "Server Connected ... \n";
        });

        $this->server->on('receive',function($this->server,$fd,$form_id,$data){
            //$this->server->send($fd,$data);
            $process = new ServerProcess($this->server,$fd,$data);
            //$this->server->close();
        });

        $this->server->on('close',function($this->server,$fd){
            echo "Server Closed ... \n";
        });
    }



    public function init(){
        $this->server->start();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

}

$server = new TcpServer();
$server->init();