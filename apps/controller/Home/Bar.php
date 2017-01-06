<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/29
 * Time: 15:41
 */

namespace controller\Home;


use ZPHP\Core\Config;
use ZPHP\Controller\Controller;
use ZPHP\Core\Log;
use ZPHP\Core\Db;

/**
 * Class Bar
 * @package controller\Home
 * 处理来自B端的所有请求
 */
class Bar extends Controller{
    public $isApi = true;
    private  $result = ['errCode'=>1,'msg'=>'default error:some thing wrong,try again!'];
    /**
     * B===>> P ===>>S
     * url:http://server.yeleonline.com:9988/bar/winesave
     * 存酒：将已经存入数据库的酒品清单数据发送到S端
     * DATA:
     * {
        "name": "zhen",
        "seller_id": "B1111",
        "phone": "13929561341",
        "code": "bbb",
        "time": "2017-01-28",
        "info": [
            {
                "id": "1",
                "name": "洋酒And",
                "unit": "瓶",
                "count": "3",
                "percent": "1",
                "remark": ""
            },
            {
                "id": "2",
                "name": "红酒Bracelet",
                "unit": "支",
                "count": "1",
                "percent": "25%",
                "remark": ""
            }
        ]
      }
     *
     */
    public function winesave(){
        Log::write("Start Winesave...");

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        log::write("$rawData");
        if(!empty($rawData)){
            $url = $Uri['server'].$urls['winesave'];
            $re = httpPost($url,$rawData);
            Log::write($re);
            if($re){
                $this->result['errCode'] = 2 ;
                $this->result['msg'] = 'Post Error:post data to server error!';
            }else{
                $this->result['errCode'] = 0;
                $this->result['msg'] ='Send data successed!';
            }
        }
        return $this->result;
    }


    /**
     * B===>> P ===>>S
     * url:http://server.yeleonline.com:9988/bar/fetchfinish
     * 接收B端取酒成功信息，将数据发送至S端，重试5次机制
     * DATA:
     * {
        "phone":"123456789",
        "code":"code",
        "status":1,
        "msg":"错误信息"
       }
     *
     * @return array|mixed
     *
     */
    public function fetchfinish(){

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        if(!empty($rawData)){
            $url = $Uri['server'].$urls['winesave'];
            $re = httpPost($url,$rawData);
            if($re){
                $this->result['errCode'] = 2 ;
                $this->result['msg'] = 'Post Error:post data to server error!';
            }else{
                $this->result['errCode'] = 0;
                $this->result['msg'] ='Send data successed!';
            }
        }
        return $this->result;
    }

    /**
     * B===>> P ===>>S
     * url:http://server.yeleonline.com:9988/bar/savenotice
     * 用于B端确认由S端发起的存酒成功：当存酒员确认收到酒品，收入至酒架后确认，将消息发送至P端，P端通知S端存酒卡生效
     * DATA：
     * {
        "phone":"123456789",
        "code":"code",
        "status::1,
        "msg":"错误信息"
       }
     * @return array
     */
    public function savenotice(){
        Log::write("Start Winesave...");

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        log::write("$rawData");
        if(!empty($rawData)){
            $url = $Uri['server'].$urls['winesave'];
            $re = httpPost($url,$rawData);
            Log::write($re);
            if($re){
                $this->result['errCode'] = 2 ;
                $this->result['msg'] = 'Post Error:post data to server error!';
            }else{
                $this->result['errCode'] = 0;
                $this->result['msg'] ='Send data successed!';
            }
        }
        return $this->result;
    }

    /**
     * B===>> P ===>>MySQL
     * url:http://server.yeleonline.com:9988/bar/savegoods
     * 用于B端将商品信息同步至P端，保存至MySQL。
     * DATA：
     * {
        "phone":"123456789",
        "code":"code",
        "status::1,
        "msg":"错误信息"
      }
     * @return array
     */
    public function savegoods(){
        Log::write("Start GoodsSave...");

        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();

        log::write("$rawData");
        if(!empty($rawData)){
            $goods = asintogoods($rawData);
            $re = yield Db::table('goods')->save($goods);
            Log::write($re);
            if($re){
                $this->result['errCode'] = 2 ;
                $this->result['msg'] = 'Post Error:post data to server error!';
            }else{
                $this->result['errCode'] = 0;
                $this->result['msg'] ='Send data successed!';
            }
        }
        return $this->result;
    }

    /**
     * B===>> P ===>>MySQL
     * url:http://server.yeleonline.com:9988/bar/savegoods
     * 用于B端将商品信息同步至P端，保存至MySQL。
     * DATA：
     * {
        "phone":"123456789",
        "code":"code",
        "status::1,
        "msg":"错误信息"
      }
     * @return array
     */
    public function savemsg(){
        Log::write("Start GoodsSave...");


        $rawData = $this->request->rawContent();

        log::write("$rawData");
        if(!empty($rawData)){
            $msg = json_decode($rawData);
            $re = yield Db::table('messages')->save($msg);
            Log::write($re);
            if($re){
                $this->result['errCode'] = 2 ;
                $this->result['msg'] = 'Post Error:post data to server error!';
            }else{
                $this->result['errCode'] = 0;
                $this->result['msg'] ='Send data successed!';
            }
        }
        return $this->result;
    }

    public function asintogoods($data)
    {
        //TODO
        return $data;
    }
}