<?php
define("ROOTPATH",dirname(dirname(__FILE__)));
require_once ROOTPATH . '/server/config/config.php';
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/21
 * Time: 16:10
 */


class TcpClient
{
    private $client;


    public function __construct()
    {
        global $config;
        //var_dump($config);
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);


        $this->client->on('connect',function($cli){
            self::register();
            sleep(2);
            self::login();
            while(true){
              sleep(2);
              $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
              $re = $this->client->send(json_encode($data));
              if(!$re){
                continue;
              }
            }
        });

        $this->client->on('receive',function($cli,$data){
            //TODO
            var_dump($data);
            echo "you got your data:".$data;
        });

        $this->client->on('close',function($cli){
            echo "Client Closed ...\n";
        });

        $this->client->on("error", function($cli){
            echo "Connect failed\n";
        });
    }

    public function run(){
        global $config;
        $this->client->connect(
          $config['client']['host'],
          $config['client']['port'],
          $config['client']['timeout']
      );
    }

    function register(){
      $data = array('fd'=>'B999999_','cmd'=>'register');
      $re = $this->client->send(json_encode($data));
      var_dump($re);
      while(!$re){
        sleep(2);
        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'register','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }

    function login(){
      $data = array('fd'=>'B999999_','cmd'=>'login');
      $re = $this->client->send(json_encode($data));
      while(!$re){
        sleep(2);
        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'login','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
        $re = $this->client->send(json_encode($data));
        if(!$re){
          break;
        }
      }
    }
}

$client = new TcpClient();
$client->run();
