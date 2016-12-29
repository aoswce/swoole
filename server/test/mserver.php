<?php
  /**
   *@file mserver.php
   *@encoding UTF-8
   *@version 1.0
   */

   //Server
   $serv = new swoole_server('0.0.0.0',9501);

   //redis
   $redis = new Redis();
   $redis->connect('192.168.238.137',6379);

   //server
   $serv->on('start',function($serv){
     echo "Service Start...\n";
   });

   $serv->on('connect',function($serv,$fd){
     echo "Fd-[{$fd}]: Connected...\n";
   });

   $serv->on('receive',function($serv,$fd,$form_id,$data){
     $data = (array)json_decode($data);
     echo '|';
     var_dump($data);
     echo '|';
     if(!empty($data)){

     global $redis;

     $cmd = $data['cmd'];
     if(empty($cmd) || !in_array($cmd,array('login','chat'))){
       echo "Please set the right cmd!\n";

     }
     if($cmd == 'login'){
       login($data,$fd,$redis);
     }else{
       login($data,$fd,$redis);
       $recv = unserialize($redis->get($data['recv']));
       if($recv['serv_ip']!='192.168.238.132'){
         $proxy = unserialize($redis->get('192.168.238.132_router'));
         $data['recv_ip'] = $recv['serv-ip'];
         $serv->send($proxy['fd'],json_encode($data));
       }else{
         $serv->send($recv['fd'],"{$data['sender']} send you one message:{$data['content']}\n");
       }
     }

   }else{
     echo "Please Enter your Opt!\n";
   }
   });

   $serv->on('close',function($serv,$fd){
     echo "Service Closed/Stoped...\n";
   });

   function login($data,$fd,$redis){
     $login_status = $redis->get($data['name']);
     echo "Client Name:[{$data['name']}]\n";
     var_dump($login_status);
     if(!empty($login_status)){
       echo "You have been Logined!\n";
     }else{
       if(isset($data['name'])){
        $save = array(
          'fd'=>$fd,
          'serv_ip'=>'192.168.238.132'
        );
        $redis->set($data['name'],serialize($save));
        echo "Login Successed!\n";
      }else{
        echo "Client Name not set!\n";
      }
     }
   }


   $serv->start();


?>
