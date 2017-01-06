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



class TcpClient
{
    private $client;


    public function __construct()
    {

        //var_dump($config);
        // fpm : SWOOLE_KEEP
        $this->client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);



        $this->client->on('connect',function($cli){
            var_dump($cli);

            global $config;
            echo "Server-Client:Start...\n";
            swoole_set_process_name('Yserver' . ' Client running ' .
                'TCP'.
                '://' . $config['client']['host'] .
                ':' . $config['client']['port']
                . " time:".date('Y-m-d H:i:s')."  master");

            self::register();
            sleep(1);
            self::login();
            sleep(1);



            //循环检测队列，将通知触发至服务
            $i=0;
            while(true){
                //从Redis获取要发送的数据
                $redis = self::getRedis();
                echo "==================Redis>> ===================\n";
                var_dump($redis);
                echo "==================Redis>> ===================\n";

                $i++;
                echo "==================sc while [{$i}]===================\n";

                echo "==================sc while===================\n";
                sleep(1);

                try{
                    //var_dump($redis);
                    if(is_object($redis) && $redis->ping()=='PONG'){
                        echo "==================Redis if ===================\n";
                        $sends = $redis->keys('S:*:*');
                        var_dump($sends);
                        unset($redis);
                        //$redis->close();
                    }else{
                        echo "==================Redis else ===================\n";
                        //$redis->close();
                        unset($redis);
                        continue;
                    }
                    //如果有数据将数据发送动作发送给服务端
                    $num = count($sends);
                    if($num){
                        echo "==================count(\$sends)[{$num}]===================\n";

                        $data = array('fd'=>'B999999_12aew4qqwa23q','cmd'=>'sendClient','data'=>array('cmd'=>'login','user'=>'wvv','pass'=>'123456'));
                        $re = $this->client->send(json_encode($data));
                        if($re){continue;}else{//失败重发
                            $this->client->send(json_encode($data));
                        }
                    }
                }catch (Exception $e){
                    echo "==================Redis Exception ===================\n";

                    var_dump($e);
                    echo "【".$e->getCode().":".$e->getMessage()."】\n";
                    echo "==================Redis Exception ===================\n";
                    continue;
                }
            }
        });

        $this->client->on('receive',function($cli,$data){
            echo "you got your data:".$data."\n";
            var_dump($cli);
        });

        //$this->client->on('task',array($this,'onTask'));

        $this->client->on('close',function($cli){
            echo "Client Closed ...\n";
            var_dump($cli);
        });

        $this->client->on("error", function($cli){
            echo "Connect failed\n";
            var_dump($cli);
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
        ini_set('default_socket_timeout', -1);
        $redis = [];

        try{
            for($i = 0; $i<100;$i++){
                $redis[$i] = new Swoole\Redis;

                $redis[$i]->connect(
                    $config['redis']['master']['host'],
                    $config['redis']['master']['port']
                );
            }
        }catch (Exception $e){
            echo "==================Redis Connect Exception ===================\n";
            echo $e->getCode().":".$e->getMessage().$e->getTraceAsString()."\n";
            echo "==================Redis Connect Exception ===================\n";
            print_r($e->getMessage());
        }
        $re = $redis[rand(0,99)];
        if(!empty($re) && is_object($re)){
            if($re->ping()=='PONG'){
                return $re;
            }else{
                return $this->getRedis();
            }
        }else{
            return $this->getRedis();
        }
    }

}

$client = new TcpClient();
$client->run();
