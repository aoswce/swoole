<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21
 * Time: 16:30
 */

namespace com\yele\service;


class ServerProcess
{
    private $flag = false;
    private $re;

    public function __construct($server,$fd,$data)
    {

        if(is_null($server) || !is_a(Swoole\Server)){
            echo "Server is wrong!";
            $this->flag = true;
            $this->re['code']='s01';
        }

        if(is_null($fd) || empty($fd)){
            echo "Fd is error!";
            $this->flag = true;
            $this->re['code']='s01';
        }

        if(is_null($data) || empty($data)){
            echo "Server get the wrong data!";
            $this->flag = true;
            $this->re['code']='s01';
        }
        $result = (array)json_decode($data);
        /**
         * [
         *  'source':sock|http  sock表示来自于客户端的请求，http表示来自httpserver的请求
         *  'content':{

         * }
         *  'opt':'datasync|msgsend|request_todo'
         *
         * ]
         */

        $msg = self::process($result,$this->re);
        return $this->re;
    }

    protected function process($resut,$msg)
    {
        switch ($resut['source']){
            case 'sock':
                date_date_set();
                break;

            case 'http':
                ddd;
                break;

            default:
                array();
                break;
        }
    }

}