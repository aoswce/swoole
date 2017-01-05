<?php
/**
 * Created by PhpStorm.
 * User: Avine
 * Date: 2016/12/21
 * Time: 16:10
 */

define("ROOTPATH",dirname(dirname(__FILE__)));
require_once ROOTPATH . '/server/config/config.php';
require_once ROOTPATH . '/server/function/function.php';

require ROOTPATH.'/vendor/autoload.php';
use ZPHP\ZPHP;

define('DEBUG', true);

use ZPHP\Core\Log;

class TcpClient
{
    private $client;


    public function __construct()
    {
        //var_dump($config);
        // fpm : SWOOLE_KEEP
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);


        $this->client->on('connect',function($cli){
            Log::write("==================sc while===================Server-client redis scan...==================sc while===================\n");
            global $config;
            echo "Server-Client:Start...\n";
            swoole_set_process_name('Yserver' . ' Client running ' .
                'TCP'.
                '://' . $config['client']['host'] .
                ':' . $config['client']['port']
                . " time:".date('Y-m-d H:i:s')."  master");

            self::register();
            sleep(2);
            self::login();

            //从Redis获取要发送的数据
            $redis = self::getRedis();
            echo "==================Redis>> ===================\n";
            var_dump($redis);
            echo "==================Redis>> ===================\n";
            //循环检测队列，将通知触发至服务
            $i=0;
            while(true){
                $i++;
                echo "==================sc while [{$i}]===================\n";
                Log::write("Server-client redis scan...\n");
                echo "==================sc while===================\n";
                sleep(1);
                try{
                if($redis){
                    echo "==================Redis if ===================\n";
                    $sends = $redis->keys('S:*:*');
                    $redis->close();
                }else{
                    echo "==================Redis else ===================\n";
                    $redis->close();
                    continue;
                }
                //如果有数据将数据发送动作发送给服务端
                if(count($sends)){
                    echo "==================count(\$sends)[{$sends}]===================\n";
                    Log::write("Server-client Send cmd: [sendClient]\n");
                    $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
                    $re = $this->client->send(json_encode($data));
                    if($re){continue;}else{//失败重发
                        $this->client->send(json_encode($data));
                    }
                }
                }catch (Exception $e){
                    echo "==================Redis Exception ===================\n";
                    Log::write("Server-client Error: ");
                    continue;
                }
            }
        });

        $this->client->on('receive',function($cli,$data){
            echo "you got your data:".$data."\n";
        });

        //$this->client->on('task',array($this,'onTask'));

        $this->client->on('close',function($cli){
            echo "Client Closed ...\n";
        });

        $this->client->on("error", function($cli){
            echo "Connect failed\n";
        });
    }

    public function send(){
        //从Redis获取要发送的数据
        $redis = self::getRedis();
        $i = 0;
        //循环检测队列，将通知触发至服务
        while(true){
            echo "you got your data times => ". $i;
            sleep(1);
            $sends = $redis->keys('S:*:*:*');
            //如果有数据将数据发送动作发送给服务端
            if(count($sends)){
                $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
                $re = $this->client->send(json_encode($data));
                if($re){continue;}else{//失败重发
                    $this->client->send(json_encode($data));
                }
            }
            $i++;
        }
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

    private function getRedis(){
        global $config;
        $redis = new Redis;
        try{
            $re = $redis->connect(
                $config['redis']['master']['host'],
                $config['redis']['master']['port']
            );
        }catch (Exception $e){
            print_r($e->getMessage());
        }
        return $redis;
    }
}

$client = new TcpClient();
$client->run();
