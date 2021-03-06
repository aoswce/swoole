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
use ZPHP\Core\Db;
use ZPHP\Core\Log;

/**
 * Class Bar
 * @package controller\Home
 * 处理来自S端的所有请求
 */
class Trans extends Controller{
    public $isApi = true;
    private  $result = ['errCode'=>1,'msg'=>'default error:some thing wrong,try again!'];


    /**
     * S===>> p ===>> B
     * url:http://server.yeleonline.com:9988/trans/winefetch
     * 用户取酒，发送通知至P端，由P端将消息存储在队列，由server转发至B端client。
     * @return array|mixed
     * DATA:
     * {
        "serller_id": "1111",
        "phone": "123456789",
        "code": "code",
        "sales": [
            {
            "phone": "123456789",
            "name": "xxxx"
            }
        ]
      }
     */
    public function winefetch(){
        Log::write("Server fetch wine Start...");
        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        Log::write("Send Data:".($rawData));
        Log::write("Send Data:".json_encode($rawData));

        if(!empty($rawData)){
            //此处数据保存至Redis
            Log::write("==============77777777777777===========");
            $data = json_decode($rawData);
            //$re = self::saveData("wine:save:".$data['seller_id'],$data);
            $re = yield saveData("wine:fetch",$rawData);
            Log::write("===========888888888888888888888888==============");
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
     * S===>> p ===>> B
     * url:http://server.yeleonline.com:9988/trans/winesave
     * 用户或者营销自己存酒，P端接收存酒信息，传至B端Client，由client存储至Mysql并发消息至Redis通知队列
     * DATA:
     * {
        "seller_id":"1111",
        "customer_name": "张三",
        "customer_cellphone": "12345678901",
        "marketing_cellphone": "marketing_cellphone",
        "label_num": "label_num",
        "source": "source",
        "list": [
                {
                    "goods_id": 1,
                    "goods_name": "洋酒",
                    "goods_unit": "支",
                    "goods_count": 1,
                    "goods_percent": 1,
                    "goods_remark": "xxxxx"
                },
                {
                    "goods_id": 1,
                    "goods_name": "红酒",
                    "goods_unit": "支",
                    "goods_count": 3,
                    "goods_percent": 1,
                    "goods_remark": "yyyyy"
                }
            ]
       }
     * @return array|mixed
     */
    public function winesave(){
        Log::write("Server save wine Start...");
        $Uri = Config::get('uri');
        $urls = $Uri['urls'];
        $rawData = $this->request->rawContent();
        Log::write("Send Data:".($rawData));
        Log::write("Send Data:".json_encode($rawData));

        if(!empty($rawData)){
            //此处数据保存至Redis
            Log::write("==============77777777777777===========");
            $data = json_decode($rawData);
            //$re = self::saveData("wine:save:".$data['seller_id'],$data);
            $re = yield saveData("wine:save",$rawData);
            Log::write("===========888888888888888888888888==============");
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

    public function innerFunction(){
        Log::write("Inner function...");
    }


}
