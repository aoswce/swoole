<?php
/**
 * Created by PhpStorm.
 * User: zhaoye
 * Date: 2016/10/27
 * Time: 上午9:55
 */

namespace controller\Home;

use service\TestService;
use ZPHP\Controller\Controller;
use ZPHP\Core\App;
use ZPHP\Core\Factory;
use ZPHP\Core\Log;
use ZPHP\Coroutine\Http\HttpClientCoroutine;
use ZPHP\Core\Db;
use ZPHP\Redis\Redis;
use ZPHP\Route\Route;

class Test extends Controller{
    public $isApi = true;

    public function index($abcd='abcd'){
        $data['list'] = yield App::service('test')->test($abcd);
        $data['request'] = $_REQUEST;
        return $data;
    }
    /**
     * service 封装方法
     * @return mixed
     */
    public function service(){
        //使用1-封装在service层,需要yield
        $testservice = new TestService();
        $vo = yield $testservice->test(1);
        $data['vo'] = $vo;
        return $data;
    }


    /**
     * table 使用方法
     * @return mixed
     */
    public function table(){
        $user = yield table('admin_user')->where(['id' => 2])->find();
        $res['user'] = $user;
        return $res;
    }

    /**
     * 异步http client使用方法
     * @return array
     */
    public function httpClient(){
        $httpClient = new HttpClientCoroutine();
        $data = yield $httpClient->request('http://speak.test.com/');
        return ['html'=>$data];
    }

    /**
     * cache的写法
     */
    public function cache(){
        //使用2 - 写缓存
        //yield Db::redis()->cache('abcd1',1111);
        // 读缓存
        $data = yield Db::redis()->cache('abcd1');
        $res['cache'] = $data;
    }


    public function test($id){
        $data = yield Db::redis()->cache('S:'.$id.':wine:save');
        if(empty($data)){
          $re = yield Db::redis()->cache('S:'.$id.':wine:save','true');
          if(!$re){
            return ['key'=>'S:'.$id.':wine:save','bid'=>$id,'result'=>'Failed'];
          }
        }
        return ['key'=>'S:'.$id.':wine:save','bid'=>$id,'result'=>'Successed'];
    }

    public function testpost(){
        //$datas['post'] = $this->input->post();
        //$datas['reqt'] = $this->input->request();
        var_dump($this);
        $datas['raw'] = $this->request->rawContent();
        $datas['post'] = $this->request->post;
        var_dump($datas);
        $re = yield Db::redis()->cache('S:9999:wine:save',json_encode($datas));
        //$datas['redis']=$data;
        return ['data'=>$datas];
    }
}
