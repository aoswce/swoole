<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2016/11/28
 * Time: 下午1:55
 */


return [
    'route'=>[
        'GET' => [
            '/testindex' => function(){return 111;},
        ],
        'POST' => [
            '/testinfo/save' => function(){
                $data = yield \ZPHP\Core\App::controller('home\test')->testpost();
                return ['data'=>$data];
            },
        ],
        'ANY' => [
            '/' => 'Index\main',
            '/index/main' => 'Index\main',
            '/user/{name}/no/{id}' => function($id, $name){
                $data = yield \ZPHP\Core\App::controller('home\index')->user($id, $name);
                return ['data'=>$data];
            },

            '/user/{id}' => function($id){
                return \ZPHP\Core\App::controller('home\index')->user($id);
            },

            '/index/test/{id}' =>  function($id){
              $data = yield \ZPHP\Core\App::controller('home\test')->test($id);
              return ['data'=>$data];
            },
            '/index/testpost'    =>  function(){
              $data = yield \ZPHP\Core\App::controller('home\test')->testpost();
              return ['data'=>$data];
            },

        ],
    ],
];
