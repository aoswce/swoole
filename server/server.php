<?php
namespace com\yele\server;
use ZPHP\Core\Db;
use ZPHP\Core\Log;
use Swoole;

define("ROOTPATH",dirname(dirname(__FILE__)));
require_once ROOTPATH . '/server/config/config.php';
require_once ROOTPATH . '/server/function/function.php';
#require __DIR__.'/redis-async/src/Swoole/Async/RedisClient.php';
require ROOTPATH.'/vendor/autoload.php';

global $config;
define('proxy_enable',$config['proxy_enable']);
define('DEBUG', true);


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

      $this->serv->addlistener(
                            $config['server']['slave']['host'],
                            $config['server']['slave']['port'],
                            SWOOLE_SOCK_TCP
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

      $this->serv->on('Shutdown',array($this,'OnShutdown'));

      //监听客户端服务连接
      $this->serv->on('connect', array($this, 'onConnect'));

      //监听服务接收
      $this->serv->on('receive', array($this, 'onReceive'));

      //必须在onWorkerStart回调中创建redis/mysql连接
      $this->serv->on('Workerstart', array($this, 'onWorkerstart'));
      $this->serv->on('Workerstop', array($this, 'onWorkerstop'));


      $this->serv->on('ManagerStart', array($this, 'onManagerStart'));

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


  /**
   * data => array('fd'=>'B110_securekey','cmd'=>'register',data=>array())
   * cmd-pre : regiser/login/
   * cmd-opt : savewine/getwine/getgoods/sendgoods/...
   *

  **/
  function onTask($serv,$task_id,$from_id,$data){
      echo "Start Task:[task ID:{$task_id}]>[fromID:{$from_id}]!\n";
      var_dump($data);

      $fd_arr = explode('_',$data['fd']);
      $fdPre = $fd_arr[0]."_";
      //如果没有注册不存在$secureKey
      //$fdEnd = $secureKey = $fd_arr[1];

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
            echo  "You have got Registered!\n";
          }
          break;


        case 'login':
          $logKey = $fdPre."log_".$data['fds']."_";
          if(empty($redis->get($logKey))){
            $re = $redis -> set($logKey,json_encode(array('fd'=>$data['fds'],'time'=>date('Y-m-d H:i:s',strtotime('now')),'ip'=>'')));
            if($re){
              $this->serv->send($data['fds'],"You have got Logined Success!");
              echo  "You have got Logined Success!\n";
            }else{
              $this->serv->send($data['fds'],"You have got Logined Failed!");
              echo  "You have got Logined Failed!\n";
            }
          }else{
            $this->serv->send($data['fds'],"You have got Logined!");
            echo  "You have got Logined!\n";
          }

          break;
          /*
                  case 'dbquery':
                      $re = yield Db::table('user')->where(['id'=>1])->find();
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
              $re = $redis->keys('B'.$fd_toB.'_log_*_');
              $bkey = "";
              foreach ($re as $k => $v) {
                echo "[$k]=>[$v]";
                $bkey = $v;
              }
              $fd_toBkey = explode("_",$bkey);
              $fd_to = $fd_toBkey[2];
              //发送数据到客户端，客户端收到后再确认，再将Redis数据清除
              $this->serv->send($fd_to,$value);
            }
            break;
*/
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
      //var_dump($data);
  }

  /**
   * 监听消息接收：当收到客户端的消息后，对消息进行处理
   * $data : 是客户端发送的数据，需要进行解析
   * $fd : 对应的客户端标识，此处的客户端应该是对应的商家端B，一个商家端只维持一个有效的连接，需要通过FD及发送的数据进行商家端辨识
   * $serv : 当前服务端标识
   * $from_id : from_id是来自于哪个reactor线程，目前尚未用到
   */
  function onReceive($serv,$fd,$from_id,$data){
      Log::write("Server Receive ...");
      $data = (array)json_decode($data);
      echo "==============recv data======================";
      var_dump($data);
      echo "==============recv data======================";
      $info = $serv->connection_info($fd, $from_id);
      echo "Get Received From 【{$info['remote_ip']}】: 【{$data['cmd']}】 Request:[fromID:{$from_id}][fd:{$fd}]connected!\n";
      echo "==============info======================";
      var_dump($info);
      echo "==============info======================";

      if($info['server_port'] == 9502) {//来自9502的内网管理端口
          $serv->send($fd, "welcome admin\n");
      }else {                           //来自外网9501的端口
          $data['fds']=$fd;

          if(!empty($data['fd']) && self::validate($data['fd'])){
              $this->serv->task($data);
          }else{
              echo "Error Data Here!";
          }
      }

  }

    /**
     * 服务端启动时，完成数据库连接池的初始化
     * @param $serv
     */
  function onStart($serv){
      global $config;
      echo "Server:Start...\n";
      swoole_set_process_name('Yserver' . ' Server running ' .
          'TCP'.
          '://' . $config['server']['master']['host'] .
          ':' . $config['server']['master']['port']
          . " time:".date('Y-m-d H:i:s')."  master:" . $serv->master_pid);

      Log::write("Server Start ...");
  }

  function OnShutdown($serv){
      echo "Server:Stoped Master[{$serv->master_pid}]...\n";
  }
    /**
     * @param $server
     * @throws \Exception
     * @desc 服务启动，设置进程名
     */
    function onManagerStart($serv){
        swoole_set_process_name('Yserver ' .
            'Server manager:' . $serv->manager_pid);
    }

    function onWorkerstart($serv, $wid) {
        global $config;
        if($serv->taskworker){
            swoole_set_process_name('Yserver' . " Server tasker  num: ".($wid - $config['runparams']['task_worker_num'])." pid " . $serv->worker_pid);
        }else{
            swoole_set_process_name("Yserver". " Server worker  num: {$serv->worker_id} - {$config['runparams']['worker_num']}. pid " . $serv->worker_pid);
        }

        //parent::onWorkerStart($server, $workerId);
        $common = Config::get('common_file');
        if(!empty($common)){
            require ROOTPATH.$common;
        }
        if (!$serv->taskworker) {
            //worker进程启动协程调度器
            //work一启动加载连接池的链接、组件容器、路由
            Db::getInstance()->initMysqlPool($wid, Config::getField('database','master'));
            Db::getInstance()->initRedisPool($wid, Config::get('redis'));
            Db::getInstance()->initSessionRedisPool($wid, Config::get('session'));
            App::init(Factory::getInstance(\ZPHP\Core\DI::class));
            Route::init();
            Session::init();
            $this->coroutineTask = Factory::getInstance(\ZPHP\Coroutine\Base\CoroutineTask::class);
            $this->dispatcher = Factory::getInstance(\ZPHP\Core\Dispatcher::class);
            $this->requestDeal = Factory::getInstance(\ZPHP\Core\Request::class, $this->coroutineTask);
        }
    }

    /**
     * @param $server
     * @param $workerId
     */
    function onWorkerstop($server, $workerId){
        if(!$server->taskworker) {
            Db::getInstance()->freeMysqlPool();
            Db::getInstance()->freeRedisPool();
        }
        parent::onWorkerStop($server, $workerId);
    }


  function onConnect($serv,$fd){
      //TODO : 客户端连接时应该将客户的信息记录到Redis，用于统计连接的客户端
      echo "Client:{$fd}connected!\n";
  }

  function onClose($serv,$fd){
      $data = array('fd'=>$fd,'cmd'=>'logout');
      $this->serv->task($data);
      echo "Client : [fd:{$fd}]Close.\n";
  }

  function validate($data){
    //TODO:
    echo "validate:".$data."\n";
    return true;
  }

    /**
     * 创建 redis
     * @return Redis
     */
  function getRedis(){
      global $config;
      $redis = new Redis;
      $redis->connect(
            $config['redis']['master']['host'],
            $config['redis']['master']['port']
      );

    return $redis;
  }

}

$server = new Server();
?>
