<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/30
 * Time: 10:06
 */

use \ZPHP\Socket\Adapter\Swoole;

return array(
    'server' => 'http://api.app-server-online.yele/seller/',
    'proxy'=>'http://server.yeleonline.com:9988/',

    'urls' => array(
        'savenotice' =>  'savenotice',                        //APP创建的存酒卡生效通知
        'winesave'   =>  'savewine',                          //商家存酒
        'winefetch'  =>  'getwine',                           //商家取酒
    ),

    'urlc'=>array(
        'type' => 'api',
    ),

    'urlp'=>array(
        'winefetch' =>  '/bar/winefetch',           //用户取酒，将信息存储R，由服务Server发出至B。
    )

);
