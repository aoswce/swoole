<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 16/7/15
 * Time: 下午3:58
 */

namespace controller\Home;

use ZPHP\Controller\Controller;
use ZPHP\Core\Db;
use ZPHP\Core\Log;

class Index extends Controller{
    public $isApi = true;
    public function index($id=0){
        Log::write("index>>>",1);
        return 'hello zpi!->{$id}'.$id;

    }

    public function test($id=0){
        $id = !empty($id)?$id:$this->input->get('id');

        $data = yield Db::redis()->decr('abcd1');
//        $data = json_decode($data, true);
        return ['data'=>$data,'request'=>$this->input->request(),'id'=>$id];
    }

    public function user($id, $name=''){
        $user = yield Db::table('user')->where(['id'=>$id])->find();
        return ['user'=>$user];
    }

    public function main(){
        Log::write("You request this index page's main method:好吧，你访问了这个方法，请看日志吧！>>>",1);
        return 'hello, Welcome here!';
    }

}
