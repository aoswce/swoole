<?php

if (!class_exists('swoole_http_server')) {
    if (!class_exists('Swoole\Http\Server')) {
        echo "請確認是否已安裝Swoole\n";
    }else{
        echo "請編輯php.ini中:\n    swoole.use_namespace=off\n";
    }
}

class XServer extends swoole_http_server
{
    public $_GLOBAL_SESSION     = [];
    public $route               = [
                                    'get'=>[],
                                    'post'=>[],
                                    'put'=>[],
                                    'head'=>[],
                                    'delete'=>[]
                                  ];

    /**
     * start
     * @param  string       $action
     * @param  closure      $callback
     */
    public function start() {
        parent::on('request', function($req, $res){
            $do = $this->initRequest($req, $res, $this);
            $req = $do['req'];
            $res = $do['res'];
            //var_dump($do);
            echo strtolower($req->server['request_method']),">",$req->server['request_uri'],"\n";
            if(isset($this->route[strtolower($req->server['request_method'])][$req->server['request_uri']])){
                return call_user_func_array(
                    $this->route[strtolower($req->server['request_method'])][$req->server['request_uri']],
                    [$req, $res, $do['get'], $do['post'], $do['server'], $do['session'], $this->_GLOBAL_SESSION, $ip]
                );

            }else{
              return call_user_func_array(
                    $this->route['error']['404'],
                    [$req, $res, $do['get'],$do['post'], $do['server'], $do['session'], $this->_GLOBAL_SESSION, $ip]
                );
            }
        });

        parent::start();
    }

    /**
     * all,get,post,put,head,delete通過本函數綁定路由
     * @param string    $method
     * @param string    $path
     * @param closure   $callback
     */
    public function path($method, $path, $callback){
        $this->route[$method][$path] = $callback;
    }

    /**
     * 綁定所有路由
     * @param string    $path
     * @param closure   $callback
     */
    public function all($path, $callback){
        foreach (['get','post','head','put','delete'] as $method)
            $this->path($method, $path, $callback);
    }

    /**
     * GET路由
     * @param string    $path
     * @param closure   $callback
     */
    public function get($path, $callback){
        $this->path('get', $path, $callback);
    }

    /**
     * POST路由
     * @param string    $path
     * @param closure   $callback
     */
    public function post($path, $callback){
        $this->path('post', $path, $callback);
    }

    /**
     * PUT路由
     * @param string    $path
     * @param closure   $callback
     */
    public function put($path, $callback){
        $this->path('put', $path, $callback);
    }

    /**
     * HEAD路由
     * @param string    $path
     * @param closure   $callback
     */
    public function head($path, $callback){
        $this->path('head', $path, $callback);
    }

    /**
     * DELETE路由
     * @param string    $path
     * @param closure   $callback
     */
    public function delete($path, $callback){
        $this->path('delete', $path, $callback);
    }

    /**
     * ERROR路由
     * @param string    $path
     * @param closure   $callback
     */
    public function error($path, $callback){
        $this->path('error', $path, $callback);
    }

    public function initRequest($req, $res) {
        if (!isset($req->server)) $req->server = [];
        if (!isset($req->get)) $req->get = [];
        if (!isset($req->post)) $req->post = [];

        if (isset($req->server['accept-encoding']) && stripos($req->server['accept-encoding'], 'gzip')) {
            $res->gzip(5);
        }

        if (!isset($req->cookie) || !isset($req->cookie['sid']) || !$req->cookie['sid']) {
            $req->cookie['sid'] = md5(password_hash(time() . mt_rand(100000, 999999), 1));
            @$res->cookie('sid', $req->cookie['sid'], time() + 60 * 60 * 24 * 365 * 10, '/', '', false, true);
        }

        $_SESS_ID = $req->cookie['sid'];
        if (!isset($this->_GLOBAL_SESSION[$_SESS_ID]) || !is_array($this->_GLOBAL_SESSION[$_SESS_ID])) {
            $this->_GLOBAL_SESSION[$_SESS_ID] = [];
        }

        $_SESSION = &$this->_GLOBAL_SESSION[$_SESS_ID];

        if (isset($req->header)) {
            isset($req->header['if-none-match'])                ? $req->server['if-none-match']                     = $req->header['if-none-match']                 : false;
            isset($req->header['if-modified-since'])            ? $req->server['if-modified-since']                 = $req->header['if-modified-since']             : false;
            isset($req->header['connection'])                   ? $req->server['connection']                        = $req->header['connection']                    : false;
            isset($req->header['accept'])                       ? $req->server['accept']                            = $req->header['accept']                        : false;
            isset($req->header['accept-encoding'])              ? $req->server['accept-encoding']                   = $req->header['accept-encoding']               : false;
            isset($req->header['accept-language'])              ? $req->server['accept-language']                   = $req->header['accept-language']               : false;
            isset($req->header['upgrade-insecure-requests'])    ? $req->server['upgrade-insecure-requests']         = $req->header['upgrade-insecure-requests']     : false;
            isset($req->header['cache-control'])                ? $req->server['cache-control']                     = $req->header['cache-control']                 : false;
            isset($req->header['pragma'])                       ? $req->server['pragma']                            = $req->header['pragma']                        : false;
            isset($req->header['referer'])                      ? $req->server['referer']                           = $req->header['referer']                       : false;
            isset($req->header['x-forwarded-for'])              ? $req->server['remote_addr']                       = $req->header['x-forwarded-for']               : false;
            stripos($req->server['remote_addr'], ',')           ? $req->server['remote_addr']                       = stripos($req->server['remote_addr'],',')[0]   : false;
        }
        return ['req'=>$req, 'res'=>$res, 'session'=>$_SESSION, 'server'=>$req->server, 'get'=>$req->get, 'post'=>$req->post];
    }
}

class BaseException extends \Exception {
    var $data = [];
    function __construct($message, $code, $data = []) {
        if ($data == []) {
            $data = new \stdClass();
        }

        $this->data = $data;
        parent::__construct($message, $code);
        return $this;
    }
    function getData() {
        return $this->data;
    }
}

class QueueException extends BaseException {}
class ApiException extends BaseException {}
