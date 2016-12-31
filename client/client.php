<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21
 * Time: 16:10
 */
define("ROOTPATH",dirname(dirname(__FILE__)));
require_once ROOTPATH . '/client/config/config.php';


use ZPHP\Core\Db;


class Client
{
    private $client;
    /**
     * 用于标识商家端
     * @var
     */
    private $clientID;


    public function __construct()
    {
        global $config;
        //var_dump($config);
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        $this->clientID = $config['client']['host'];

        $this->client->on('connect',function($cli){
            self::register();
            sleep(2);
            self::login();
        });

        $this->client->on('receive',function($cli,$data){
            //TODO
            var_dump($data);
            echo "you got your data:".$data;
            //收到消息数据，完成两个步骤：
            //1、完成数据存储至相应的Mysql数据表
            //2、将通知发送至Redis队列
            self::savetomysql($data);
            self::savetoredis($data);
            //3、消息确认
            self::dataok();

        });

        $this->client->on('close',function($cli){
            echo "Client Closed ...\n";
        });

        $this->client->on("error", function($cli){
            echo "Connect failed\n";
        });

        self::run();
    }

    public function run(){
        global $config;
        $this->client->connect(
          $config['server']['master']['host'],
          $config['server']['master']['port']
      );
    }

    function dataok(){
        $data = array('fd'=>$this->clientID.'_12aew4qqwa23q','cmd'=>'dataok','key'=>'','status'=>1);
        self::send($data);
    }

    function register(){
      $data = array('fd'=>'B110_','cmd'=>'register');
      $re = $this->client->send(json_encode($data));
      var_dump($re);
      while(!$re){
        sleep(2);
        $data = array('fd'=>$this->clientID.'_12aew4qqwa23q','cmd'=>'register','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }

    function login(){
      $data = array('fd'=>'B110_','cmd'=>'login');
      $re = $this->client->send(json_encode($data));
      while(!$re){
        sleep(2);
        $data = array('fd'=>$this->clientID.'_12aew4qqwa23q','cmd'=>'login','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }


    function savetomysql($data){
        $re = yield Db::table('')->query($data);
    }

    function savetoredis($k,$d){
        $re = yield Db::redis()->cache($k,$d);
    }
}

$client = new Client();
