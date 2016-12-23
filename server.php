<?php
  require_once 'config.php';
  require_once 'function.php';

  global $config;

  //创建 swoole master server
  $serv = new swoole_server(
                        $config['server']['master']['host'],
                        $config['server']['master']['port']
                    );

  //创建 redis
  $redis = new Redis();
  $redis->connect(
              $config['redis']['master']['host'],
              $config['redis']['master']['port']
            );

  //创建 Proxy
  $proxy = new swoole_client(SWOOLE_TCP | SWOOLE_KEEP);
  $proxy->connect(
                $config['proxy']['master']['host'],
                $config['proxy']['master']['port']
                );


    //监听当前服务启动
    $serv->on('start', start);

    //监听客户端服务连接
    $serv->on('connect', connect);

    //监听服务接收
    $serv->on('receive', receive);

    //监听任务开启
    $serv->on('task',task);

    //监听任务完成
    $serv->on('finish',taskFinish);

    //关闭客户端连接:通常在长连接中不调用此方法
    $serv->on('close',close);

    //设置当前服务参数
    $serv->set(array(
        'worker_num' => 1,   //一般设置为服务器CPU数的1-4倍
        'daemonize' => 1,  //以守护进程执行
        'max_request' => 10000,
        'dispatch_mode' => 2,
        'task_worker_num' => 8,  //task进程的数量
        "task_ipc_mode " => 3 ,  //使用消息队列通信，并设置为争抢模式
        //"log_file" => "log/taskqueueu.log" ,//日志
    ));

    $serv->start();
?>
