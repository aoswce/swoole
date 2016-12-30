<?php
/**
 * Created by PhpStorm.
 * User: Avine
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
        'phone' => '123456789',         //手机号（必填）			        => 匹配验证用户
        'code' => 'code',               //存洒码（必填）		            => 匹配更新存酒信息
        'status' => 1,                  //1兑换成功，0兑换失败（必填）
        'msg' => '错误信息'              // 错误信息 ，成功不必理会（可选）
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
        'phone' => '123456789',         //手机号（必填）			        => 匹配验证用户
        'code' => 'code',               //存洒码（必填）		            => 匹配更新存酒信息
        'status' => 1,                  //1兑换成功，0兑换失败（必填）
        'msg' => '错误信息'              // 错误信息 ，成功不必理会（可选）
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
}