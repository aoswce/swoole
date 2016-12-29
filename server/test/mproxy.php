<?php
  /**
  *@file mproxy.php
  *@encoding UTF-8
  *@version 1.0
  */

  //Clients
  $clients = array();

  //Servers
  $servers = array(
    '192.168.238,132',
    '192.168.238.133',
  );
  $serv_count = count($servers);
  for($i =0; $i<$serv_count; $i++){
    $clients[$servers[$i]] = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
    $clients[$servers[$i]]->remote_ip = $servers[$i];

    $clients[$servers[$i]]->on('start',function(swoole_client $cli){
      echo "Service [{$servers[$i]}] Start...\n";
    });

    $clients[$servers[$i]]->on('connect',function(swoole_client $cli){
        $data = array(
          'cmd' => 'login',
          'name' => $cli->remote_ip."_router"
        );

        $cli->send(json_encode($data));
        echo $cli->remote_ip." Connect Success!\n";
    });

    $clients[$servers[$i]]->on('receive',function(swoole_client $cli,$ata){
      $msg = (array)json_decode($data);
      $remote_ip = $msg['recv_ip'];
      unset($msg['recv_ip']);
      global $clients;
      $clients[$remote_ip]->send(json_encode($msg));
    });

    $clients[$servers[$i]]->on('error',function(swoole $cli){
      echo "{$cli->remote_ip} error\n";
    });

    $clients[$servers[$i]]->on('close',function(swoole_client $cli){
      echo "{$cli->remote_ip} Connection Closed\n";
    });
    $clients[$servers[$i]]->connect($servers[$i],9501,0.5);
  }
 ?>
