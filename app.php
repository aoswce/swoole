<?php

define('APP_PATH', dirname(__FILE__) . '/');
require_once APP_PATH.'HTTPServer.php';
require_once APP_PATH.'requestHandler.php';
require_once APP_PATH.'jsonp.php';


$server = new HTTPServer("0.0.0.0", 3155);
$jsonp  = new JSONParse;
$redis = new Redis;

$ip = '127.0.0.1';
$server->get('/storage', function ($req, $res, $_X_GET, $_X_POST, $_X_SERVER, $_X_SESSION, $_X_GLOBAL, $ip) use ($server) {
    global $jsonp,$redis;
    try {
        $res->header('Accept','application/json');
        $res->header('Accept-Charset','utf-8');
        $res->header('Content-Type','application/json;charset=utf-8');
        //$res->end('TEST get');

        $res_arr = Logic($req,$jsonp,$redis);
        //var_dump($res_arr);
    		if(array_key_exists('code',$res_arr)){
    				$code=$res_arr['code'];
    				//$response->status($code);
    				//echo 'StCode:'.$code.PHP_EOL;
    				if(201==$code){
    					//created extra header
    					$res->header('Location',$jsonp->host.'/'.$res_arr['location']);
    				}
    			}else{
    				// 200 is the default code
    				$jsonp->fillWithArr($res_arr['arr']);
    				$ret_str = $jsonp->getEncodedStr();
    				$jsonp->cleanUp();
    		}

        $httpcontent = isset($ret_str)? $ret_str : '';
    		unset($ret_str);
    		//echo 'Ret_Cont:'.$httpcontent.PHP_EOL;
    		//var_dump($httpcontent);
    		//var_dump(gettype($httpcontent));
    		$res->end($httpcontent);
    } catch (\ApiException $e) {

    }
});


$server->post('/test/get', function ($req, $res, $_X_GET, $_X_POST, $_X_SERVER, $_X_SESSION, $_X_GLOBAL, $ip) use ($server) {
    try {
        $res->end('TEST post');
        return;
    } catch (\ApiException $e) {

    }
});

$server->delete('/test/get', function ($req, $res, $_X_GET, $_X_POST, $_X_SERVER, $_X_SESSION, $_X_GLOBAL, $ip) use ($server) {
    try {
        $res->end('TEST delete');
        return;
    } catch (\ApiException $e) {

    }
});


$server->put('/test/get', function ($req, $res, $_X_GET, $_X_POST, $_X_SERVER, $_X_SESSION, $_X_GLOBAL, $ip) use ($server) {
    try {
        $res->end('TEST put');
        return;
    } catch (\ApiException $e) {

    }
});


$server->all('/test/all/', function ($req, $res, $_X_GET, $_X_POST, $_X_SERVER, $_X_SESSION, $_X_GLOBAL, $ip) use ($server) {
    try {
        $res->end('TEST all');
        return;
    } catch (\ApiException $e) {

    }
});

function Logic($request,$json,$redis){
  // hot deployment

  $uri=$request->server['request_uri'];
  switch($request->server['request_method'])
  {
    case 'POST':
      $p_data=$json->parseRawData($request->rawContent());

      if(false!==$p_data){
        echo 'Post '.print_r($p_data,true).PHP_EOL;
        $res_arr=RequestHandler::handlePostReq($uri,$p_data,$redis);
      }else{
        // something wrong about posted data
        $res_arr=['code'=>406];
      }
      break;

    case 'DELETE':
      echo 'Del '.$uri.PHP_EOL;
      $res_arr=RequestHandler::handleDelReq($uri,$redis);
      break;

    case 'PUT':
      $p_data=$json->parseRawData($request->rawContent());
      if(false!==$p_data){
        echo 'Put '.print_r($p_data,true).PHP_EOL;
        $res_arr=RequestHandler::handlePutReq($uri,$p_data,$redis);
      }else{
        // something wrong about posted data
        $res_arr=['code'=>406];
      }
      break;

    case 'PATCH':
      $p_data=$json->parseRawData($request->rawContent());
      if(false!==$p_data){
        echo 'Patch '.print_r($p_data,true).PHP_EOL;
        $res_arr=RequestHandler::handlePatchReq($uri,$p_data,$redis);
      }else{
        // something wrong about posted data
        $res_arr=['code'=>406];
      }
      break;

    case 'GET':
      echo 'Get '.$uri.PHP_EOL;
      // search
      if(0===substr_compare($uri,'search',-6)){
        $get_args=isset($request->get) ? $request->get : null;
        $res_arr=RequestHandler::handleSearchReq($uri,$get_args,$redis);
      }else{
        $res_arr=RequestHandler::handleGetReq($uri,$redis);
      }
      break;

    default:
      // bad request
      $res_arr=['code'=>400];
      break;
  }
  return $res_arr;
}


$server->start();
