<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2016/11/29
 * Time: 下午2:14
 */

namespace controller\Home;

use ZPHP\Controller\Controller;
use ZPHP\Core\Db;

class User extends Controller{
    //Set the class to be Api
    public $isApi = true;


    public function getDetail(){
        $user = yield Db::table('user')->where(['id'=>1])->find();
        return ['users'=>$user];
    }
}
