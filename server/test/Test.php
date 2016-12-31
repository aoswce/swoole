<?php

/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/31
 * Time: 15:18
 */
use ZPHP\Core\Db;

class Test{

    public function __construct(){
        $re = yield Db::table('user')->where(['id'=>1])->find();
        var_dump($re);
    }

    public function init(){
        echo "Test init ...";
        $re = yield Db::table('user')->where(['id'=>2])->find();
        var_dump($re);
        echo "Test Finished ...";
    }

}

$test = new Test();