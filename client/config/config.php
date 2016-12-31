<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22
 * Time: 15:26
 */


$config = array(
    'runparams'=>array(
        'worker_num' => 1,   //一般设置为服务器CPU数的1-4倍
        'daemonize' => 0,  //以守护进程执行
        'max_request' => 10000,
        'dispatch_mode' => 2,
        'task_worker_num' => 8,  //task进程的数量
        "task_ipc_mode " => 3 ,  //使用消息队列通信，并设置为争抢模式
        //"log_file" => "log/taskqueueu.log" ,//日志
    ),
    'server'=>array(
        'master'=>array(
            'host'=>'116.62.46.72',
            'port'=>9501,
            'timeout'=>0.5
        ),
        'slave'=>array(

        )

    ),
    'client'=>array(
        'host'=> '1111',
        'port'=> 9501,
        'timeout'=>0.5
    ),
    'proxy_enable'=>false,
    'proxy'=>array(
        'master'=>array(
            'host'=>'192.168.238.137',
            'port'=>9501,
            'timeout'=>0.5
        ),
        'slave'=>array(

        )
    ),
    'redis'=>array(
        'master'=>array(
            'host'=>'127.0.0.1',
            'port'=>6379,
            'user'=>'',
            'pass'=>''
        ),
        'slave'=>array(

        )
    ),
    'mysql'=>array(
        'master'=>array(
            'host'=>'127.0.0.1',
            'port'=>3306,
            'user'=>'yele_admin_test',
            'pass'=>'yele_admin_test',
            'db'  =>'yele_admin_test'
        ),
        'slave'=>array(

        )
    ),

);
