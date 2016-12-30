<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/29
 * Time: 15:41
 */

namespace controller\Home;


use ZPHP\Core\Config;
use ZPHP\Controller\Apicontroller;
use ZPHP\Core\Log;

/**
 * Class Bar
 * @package controller\Home
 * 处理来自S端的所有请求
 */
class Trans extends Apicontroller{

    /**
     * S===>> p ===>> B
     * @return array|mixed
     */
    public function winefetch(){
        $re = ['status'=>1,'msg'=>'default error:some thing wrong,try again!'];

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();

        if(!empty($rawData)){
            //此处数据保存

            if($re){
                $re['status']=2;
                $re['msg']='Post Error:post data to server error!';
            }else{
                $re['status']=0;
                $re['msg']='Send data successed!';
            }
        }
        return $re;
    }

    public function savenotice(){
        $re = ['status'=>1,'msg'=>'default error:some thing wrong,try again!'];

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        if(!empty($rawData)){
            $url = $Uri['server'].$urls['winesave'];
            $re = httpPost($url,$rawData);
            if($re){
                $re['status']=2;
                $re['msg']='Post Error:post data to server error!';
            }else{
                $re['status']=0;
                $re['msg']='Send data successed!';
            }
        }
        return $re;
    }

    private function saveData($data){
        $key = "B1111";
        $re = yield Db::redis()->cache($key,$data);
        return $re;
    }
}