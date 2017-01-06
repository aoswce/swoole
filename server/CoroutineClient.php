<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/6
 * Time: 15:06
 */

$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, 0.5))
{
    exit("connect failed. Error: {$client->errCode}\n");
}

$redis = new Swoole\Coroutine\Redis();
$redis->connect('127.0.0.1', 6379);
//$val = $redis->get('key');
while(true){
    sleep(2);
    $rs = $redis->keys('S:*:*');
    var_dump($rs);
    $num = count($rs);
    if($num){
        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $client->send(json_encode($data));
    }else{
        continue;
    }
    echo "Client Recv:".$client->recv();
}
$client->close();