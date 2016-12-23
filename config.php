<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/22
 * Time: 15:26
 */


$config = array(
    'server'=>array(
        'master'=>array(
            'host'=>'0.0.0.0',
            'port'=>9501,
            'timeout'=>0.5
        ),
        'slave'=>array(

        )

    ),
    'client'=>array(),
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
            'host'=>'192.168.238.137',
            'port'=>6379,
            'user'=>'',
            'pass'=>''
        ),
        'slave'=>array(

        )
    ),
    'mysql'=>array(
        'master'=>array(
            'host'=>'',
            'port'=>3306,
            'user'=>'yele',
            'pass'=>'yele123',
        ),
        'slave'=>array(

        )
    ),

);

global $config;