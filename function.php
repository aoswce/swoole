<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22
 * Time: 16:16
 */

function task($serv,$task_id,$form_id,$data){
    echo "Start Task:[task ID:{$task_id}]>[formID:{$form_id}]!\n";
    var_dump($data);
    global $proxy;

    $proxy->send($data);
}

function taskFinish($serv,$task_id,$data){
    echo "Client:[task_id:{$task_id}]>Finished.\n";
    var_dump($data);
}

/**
 * 监听消息接收：当收到客户端的消息后，对消息进行处理
 * $data : 是客户端发送的数据，需要进行解析
 * $fd : 对应的客户端标识，此处的客户端应该是对应的商家端B，一个商家端只维持一个有效的连接，需要通过FD及发送的数据进行商家端辨识
 * $serv : 当前服务端标识
 * $form_id : from_id是来自于哪个reactor线程，目前尚未用到
 */
function receive($serv,$fd,$form_id,$data){
    echo "Get Received:[formID:{$form_id}][fd:{$fd}]connected!\n";
    var_dump($data);
    global $redis;

    $data = (array)json_decode($data);
    var_dump($serv);
    //get message commond
    $cmd = $data['cmd'];

    switch ($cmd) {
        case 'login':
            echo "Login>\n";
            //when login:save the client
            $save = array(
                'fd' => $fd,
                'socket_ip' => '192.168.238.132'
            );
            $redis->set($data['name'],serialize($save));
            break;
        case 'chat':
            echo "Chat>\n";
            $recv = unserialize($redis->get($data['recv']));
            var_dump($recv);
            if($recv['socket_ip']!='192.168.238.132'){
                $data['cmd']='forward';
                $data['recv_ip']=$recv['socket_ip'];
                $serv->task(json_encode($data));
            }else{
                $serv->send($recv['fd'],"{$data['send']} send you Messages:{$data['content']}");
            }
            break;
        case 'forward':
            echo "Forward>\n";
            $recv = unserialize($redis->get($data['recv']));
            $serv->send($recv['fd'],"{$data['send']} send you Messages:{$data['content']}");
            break;
    }
}

function start($serv){
    echo "Server:Start...\n";
    //服务启动，开始轮询Redis
    while(true){
        sleep(1);
        $serv->task("taskcallback", -1, function (swoole_server $serv, $task_id, $data) {
            echo "Task Callback: ";
            var_dump($task_id, $data);
        });
    }
}

function connect($serv,$fd){
    //TODO : 客户端连接时应该将客户的信息记录到Redis，用于统计连接的客户端
    echo "Client:{$fd}connected!\n";
    var_dump($fd);
}

function close($serv,$fd){
    echo "Client : [fd:{$fd}]Close.\n";
}