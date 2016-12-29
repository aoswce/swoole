<?php
/**
 * Created by PhpStorm.
 * User: Avine
 */


function table($tableName){
    return \ZPHP\Core\Db::getInstance()->table($tableName);
}

function collection($collectionName){
    return \ZPHP\Core\Db::collection($collectionName);
}

function httpGet($url,$data){
    if ($data) {
        $url .='?'.http_build_query($data) ;
    }
    $curlObj = curl_init();                             //初始化curl，
    curl_setopt($curlObj, CURLOPT_URL, $url);           //设置网址
    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec的结果返回
    curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curlObj, CURLOPT_HEADER, 0);           //是否输出返回头信息
    $response = curl_exec($curlObj);                    //执行
    curl_close($curlObj);                               //关闭会话
    return $response;
}

function httpPost($url,$data){
    $curlObj = curl_init();                             //初始化curl，
    curl_setopt($curlObj, CURLOPT_URL, $url);           //设置网址
    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);   //将curl_exec的结果返回
    curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curlObj, CURLOPT_HEADER, 0);           //是否输出返回头信息
    // post数据
    curl_setopt($curlObj, CURLOPT_POST, True);
    // post的变量
    curl_setopt($curlObj, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($curlObj);                   //执行
    curl_close($curlObj);                              //关闭会话
    return $response;
}

