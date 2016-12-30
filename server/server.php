<?php
define("ROOTPATH",dirname(dirname(__FILE__)));
define('proxy_enable',$config['proxy_enable']);
require_once ROOTPATH . '/server/config/config.php';
require_once ROOTPATH . '/server/function/function.php';
#require __DIR__.'/redis-async/src/Swoole/Async/RedisClient.php';



class Server{
    private $serv;
    protected $pool_size = 20;
    protected $idle_pool = array(); //空闲连接
    protected $busy_pool = array(); //工作连接
    protected $wait_queue = array(); //等待的请求
    protected $wait_queue_max = 100; //等待队列的最大长度，超过后将拒绝新的请求


  public function __construct() {
      global $config;

      //创建 swoole master server
      $this->serv = new Swoole\Server(
                            $config['server']['master']['host'],
                            $config['server']['master']['port']
                        );

      /*
      //创建 redis

        $redis = new Redis;

        $re = $redis->connect(
                    $config['redis']['master']['host'],
                    $config['redis']['master']['port']
                  );
        var_dump($re);


        $redis = new Swoole\Redis;
        $redis->connect('127.0.0.1', 6379, function ($redis, $result) {
            $redis->set('test_keys', md5('value'), function ($redis, $result) {
                $redis->get('test_keys', function ($redis, $result) {
                    //var_dump($result);
                });
            });
        });

        */


      //创建 Proxy
      if(proxy_enable){
        try{

            $proxy = new Swoole\Client(SWOOLE_TCP | SWOOLE_KEEP);
            $proxy->connect(
                          $config['proxy']['master']['host'],
                          $config['proxy']['master']['port']
                          );
        }catch(Exception $e){
            print_r($e);
        }
      }

      //监听当前服务启动
      $this->serv->on('start', array($this, 'onStart'));

      //监听客户端服务连接
      $this->serv->on('connect', array($this, 'onConnect'));

      //监听服务接收
      $this->serv->on('receive', array($this, 'onReceive'));

      //必须在onWorkerStart回调中创建redis/mysql连接
      $this->serv->on('workerstart', array($this, 'onWorkerstart'));

      //监听任务开启
      $this->serv->on('task', array($this, 'onTask'));

      //监听任务完成
      $this->serv->on('finish', array($this, 'onTaskFinish'));

      //关闭客户端连接:通常在长连接中不调用此方法
      $this->serv->on('close',  array($this, 'onClose'));

      //设置当前服务参数
      $this->serv->set($config['runparams']);

      $this->serv->start();
  }

  function onWorkerstart($serv, $wid) {
    echo "Work Id:",$wid,"\n";
    //$this->serv->task('111');
  }

  /**
   * data => array('fd'=>'B110_securekey','cmd'=>'register',data=>array())
   * cmd-pre : regiser/login/
   * cmd-opt : savewine/getwine/getgoods/sendgoods/...
   *

  **/
  function onTask($serv,$task_id,$form_id,$data){
      echo "Start Task:[task ID:{$task_id}]>[formID:{$form_id}]!\n";
      var_dump($data);
      $fd_arr = explode('_',$data['fd']);
      $fdPre = $fd_arr[0]."_";
      $fdEnd = $secureKey = $fd_arr[1];

      $redis = self::getRedis();

      switch ($data['cmd']) {
        case 'register':
          $regKey = $fdPre."reg_".$data['fds']."_";
          if(empty($redis->get($regKey))){
            $re = $redis -> set($regKey,md5('s'.rand(999,9999)));
            if($re){
              $this->serv->send($data['fds'],"You have got Register Success!");
            }else{
              $this->serv->send($data['fds'],"You have got Register Failed,Try again!");
            }
          }else{
            $this->serv->send($data['fds'],"You have got Registered!");
            echo  "You have got Registered!";
          }
          break;
        case 'login':
          $logKey = $fdPre."log_".$data['fds']."_";
          if(empty($redis->get($logKey))){
            $re = $redis -> set($logKey,json_encode(array('fd'=>$data['fds'],'time'=>date('Y-m-d H:i:s',strtotime('now')),'ip'=>'')));
            if($re){
              $this->serv->send($data['fds'],"You have got Logined Success!");
              echo  "You have got Logined Success!";
            }else{
              $this->serv->send($data['fds'],"You have got Logined Failed!");
              echo  "You have got Logined Failed!";
            }
          }else{
            $this->serv->send($data['fds'],"You have got Logined!");
            echo  "You have got Logined!";
          }

          break;
        case 'savewine':
          # code...
          break;
        case 'getwine':
          # code...
          break;
        case 'getgoods':
          # code...
          break;
        case 'dbquery':
            //没有空闲的数据库连接
            if (count($this->idle_pool) == 0) {
                //等待队列未满
                if (count($this->wait_queue) < $this->wait_queue_max) {
                    $this->wait_queue[] = array(
                        'fd' => $data['fds'],
                        'sql' => $data['sql'],
                    );
                } else {
                    $this->serv->send($data['fds'], "request too many, Please try again later.");
                }
            } else {
                $this->query($data['fds'], $data['sql']);
            }
          break;
        case 'sendClient'://S Point msg getUser
          echo  "===================\n";
          var_dump($data);
          echo  "===================\n";
          $re = $redis->keys('S:*:*:*');
          var_dump($re);
          foreach ($re as $key => $value) {
            echo "[$key]=>[$value]";
            $fd_tostr = explode(":",$value);
            $fd_toB = $fd_tostr[1];
            //$redis->delete($value);
            $re = $redis->keys('B'.$fd_toB.'_log_*_');
            $bkey = "";
            foreach ($re as $k => $v) {
              echo "[$k]=>[$v]";
              $bkey = $v;
            }
            $fd_toBkey = explode("_",$bkey);
            $fd_to = $fd_toBkey[2];
            $this->serv->send($fd_to,$value);
          }
          break;
        case 'logout':
          $re = $redis->keys('B*_log_'.$data['fd']."_");
          foreach ($re as $key => $value) {
            echo "[$key]=>[$value]";
            $redis->delete($value);
          }
          break;

        default:
          # code...
          break;

      }


  }

  function onTaskFinish($serv,$task_id,$data){
      echo "Client:[task_id:{$task_id}]>Finished.\n";
      var_dump($data);
  }

  /**
   * 监听消息接收：当收到客户端的消息后，对消息进行处理
   * $data : 是客户端发送的数据，需要进行解析
   * $fd : 对应的客户端标识，此处的客户端应该是对应的商家端B，一个商家端只维持一个有效的连接，需要通过FD及发送的数据进行商家端辨识
   * $serv : 当前服务端标识
   * $form_id : from_id是来自于哪个reactor线程，目前尚未用到
   */
  function onReceive($serv,$fd,$form_id,$data){
      echo "Get Received:[formID:{$form_id}][fd:{$fd}]connected!\n";
      $data = (array)json_decode($data);
      var_dump($data);
      $data['fds']=$fd;
      var_dump($data);
      if(!empty($data['fd']) && self::validate($data['fd'])){
        $this->serv->task($data);
      }else{
        echo "Error Data Here!";
      }
  }

    /**
     * 服务端启动时，完成数据库连接池的初始化
     * @param $serv
     */
  function onStart($serv){
      echo "Server:Start...\n";
      global $config;
      for ($i = 0; $i < $this->pool_size; $i++) {
          $db = new mysqli;
          $db->connect(
                        $config['mysql']['master']['host'],
                        $config['mysql']['master']['user'],
                        $config['mysql']['master']['pass'],
                        $config['mysql']['master']['db']
                    );

          $db_sock = swoole_get_mysqli_sock($db);

          swoole_event_add($db_sock, array($this, 'onSQLReady'));

          $this->idle_pool[] = array(
                                      'mysqli' => $db,
                                      'db_sock' => $db_sock,
                                      'fd' => 0,
                                    );
      }
      echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
  }

  function onConnect($serv,$fd){
      //TODO : 客户端连接时应该将客户的信息记录到Redis，用于统计连接的客户端
      echo "Client:{$fd}connected!\n";
      var_dump($fd);
  }

  function onClose($serv,$fd){
      $data = array('fd'=>$fd,'cmd'=>'logout');
      $this->serv->task($data);
      echo "Client : [fd:{$fd}]Close.\n";
  }

  function validate($data){
    //TODO:
    echo "validate:".$data;
    return true;
  }

    /**
     * 创建 redis
     * @return Redis
     */
  function getRedis(){
      global $config;
      $redis = new Redis;
      $re = $redis->connect(
            $config['redis']['master']['host'],
            $config['redis']['master']['port']
      );

    return $redis;
  }

    function onSQLReady($db_sock){
        $db_res = $this->busy_pool[$db_sock];
        $mysqli = $db_res['mysqli'];
        $fd = $db_res['fd'];

        echo __METHOD__ . ": client_sock=$fd|db_sock=$db_sock\n";

        if ($result = $mysqli->reap_async_query()) {
            $ret = var_export($result->fetch_all(MYSQLI_ASSOC), true) . "\n";
            $this->serv->send($fd, $ret);
            if (is_object($result)) {
                mysqli_free_result($result);
            }
        } else {
            $this->serv->send($fd, sprintf("MySQLi Error: %s\n", mysqli_error($mysqli)));
        }
        //release mysqli object
        $this->idle_pool[] = $db_res;
        unset($this->busy_pool[$db_sock]);

        //这里可以取出一个等待请求
        if (count($this->wait_queue) > 0) {
            $idle_n = count($this->idle_pool);
            for ($i = 0; $i < $idle_n; $i++) {
                $req = array_shift($this->wait_queue);
                $this->query($req['fd'], $req['sql']);
            }
        }
    }

    function query($fd, $sql){
        //从空闲池中移除
        $db = array_pop($this->idle_pool);
        /**
         * @var mysqli
         */
        $mysqli = $db['mysqli'];

        for ($i = 0; $i < 2; $i++) {
            $result = $mysqli->query($sql, MYSQLI_ASYNC);
            if ($result === false) {
                if ($mysqli->errno == 2013 or $mysqli->errno == 2006) {
                    $mysqli->close();
                    $r = $mysqli->connect();
                    if ($r === true) continue;
                }
            }
            break;
        }

        $db['fd'] = $fd;
        //加入工作池中
        $this->busy_pool[$db['db_sock']] = $db;
    }

}

$server = new Server();
?>
