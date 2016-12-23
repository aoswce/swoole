<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21
 * Time: 16:49
 */

namespace com\yele\server;


use com\yele\config\Config;

class HttpServer
{
    private $conf;
    private $hServer;
    public function __construct()
    {
        $this->conf = new Config();
        $this->hServer = new Swoole\Http\Server($this->conf->host,$this->conf->port);

        $this->hServer->on('Request',function($request,$response){
            var_dump($request->get);
            var_dump($request->post);
            var_dump($request->cookie);
            var_dump($request->files);
            var_dump($request->header);
            var_dump($request->server);
            if(is_null($request->post)){
                echo "Request is wrong!";
            }else{
                //$response->cookie("User", "Swoole");
                //$response->header("X-Server", "Swoole");
                //$response->end("<h1>Hello Swoole!</h1>");
                
            }
        });

    }

    public function init()
    {
        $this->hServer->start();
    }

}

$httpServer = new HttpServer();
$httpServer->init();