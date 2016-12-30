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

/**
 * Class Bar
 * @package controller\Home
 * 处理来自B端的所有请求
 */
class Bar extends Apicontroller{
    //Set the class to be Api
    public $isApi = true;

    /**
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
        $data = [];
        $datas['raw'] = $this->request->rawContent();
        $d = Config::get('redis');
        var_dump($d);
        $re = array('status'=>'0','msg'=>'success');
        $re['r']=$d;
        $re['d']=$datas;
        return json_encode($re);
        $re = httpPost();
    }
}