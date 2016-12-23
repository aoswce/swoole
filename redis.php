<?php

$redis = new Redis();
$redis -> connect('127.0.0.1',6379);

while(true){
	$redis -> set('ismsg',json_encode(array('a'=>rand(1,10000),'name'=>rand(1000,9999))));
      }
