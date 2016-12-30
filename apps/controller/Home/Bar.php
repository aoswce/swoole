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
 * 处理来自B端的所有请求
 */
class Bar extends Apicontroller{
    //Set the class to be Api
    public $isApi = true;

    /**
     * B===>> P ===>>S
     * 存酒：将已经存入数据库的酒品清单数据发送到S端
     * DATA:
     * {
        'name' => '张三',  // 顾客信息（可选）
        'seller_id' => 'seller_id', //商家编号 （必填）
        'phone' => '123456789',  // 顾客手机号（必填）
        'code' => 'code', //商家存酒码（必填）
        'time' => '123456', //有效期（必填）
        'info' => {}, // 存酒清单（必填）
      }*
     */
    public function winesave(){
        Log::write("Start Winesave...");
        $re = ['status'=>1,'msg'=>'default error:some thing wrong,try again!'];

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        log::write("$rawData");
        if(!empty($rawData)){
            $url = $Uri['server'].$urls['winesave'];
            $re = httpPost($url,$rawData);
            Log::write($re);
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
}