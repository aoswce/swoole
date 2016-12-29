<?php
  $serv = new swoole_server('0.0.0.0',9501);

  $serv->on('start', function($serv){
    echo "Server:Start...\n";
  });

  $serv->on('connect', function($serv,$fd){
    echo "Client:connected!\n";
    var_dump($fd);
  });

  $serv->on('receive',function($serv,$fd,$form_id,$data){
    echo "Get Received:>\n";
    var_dump($data);
    global $redis;
    $serv->task($data);
  });

  $serv->on('task',function($serv,$task_id,$form_id,$data){
    echo "Task Start:>\n";
    $forward = (array)json_decode($data);
    var_dump($forward);
    $client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);

    $client->connect($forward['recv_ip'],9501);
    unset($forward['recv_ip']);
    $client->send(json_encode($forward));
    $client->close();
  });

  $serv->on('finish',function($serv,$task_id,$data){
    echo "Client:Finished.\n";
    echo "Task ID:[{$task_id}]\n";
    var_dump($data);
  });

  $serv->on('close',function($serv,$fd){
    echo "Client:[{$fd}]Close.\n";
  });

  $serv->set(array('task_worker_num'=>4));

  $serv->start();
?>
