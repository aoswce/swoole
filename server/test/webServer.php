<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22
 * Time: 17:07
 */

$serv = new Swoole\Http\Server("127.0.0.1", 9502);

$serv->on('Request', function($request, $response) {
    var_dump($request->get);
    var_dump($request->post);
    var_dump($request->cookie);
    var_dump($request->files);
    var_dump($request->header);
    var_dump($request->server);

    $this->task($request);

    $response->cookie("User", "Swoole");
    $response->header("X-Server", "Swoole");
    $response->end("<h1>Hello Swoole!</h1>");
});
    //监听来自S或者B端的请求，将请求数据存入至Redis中，在TCPServer中对redis数据进行轮询，发现有请求数据后进行相应的处理
    //1、如果是有数据需要发送至S端，则进行CRUL操作，将数据发送至相应的接口
    //2、如果是发送到B端则利用长连接将数据发送至B端fd
    $serv->on('task',function($req){
        if(!empty($req->get)){

        }

        if(!empty($req->post)){

        }

        if(!empty($req->cookie)){

        }

        if(!empty($req->files)){

        }
    });

$serv->on('finish',function(){

});

$serv->start();