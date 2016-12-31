<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/22
 * Time: 16:16
 */
/**
 * 创建 redis
 * @return Redis
 */
function getRedis(){
    global $config;
    $redis = new Redis;
    $re = $redis->connect(
        $config['redis']['master']['host'],
        $config['redis']['master']['port']
    );

    return $redis;
}
