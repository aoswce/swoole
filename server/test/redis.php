<?php
use SWoole\Redis;

$redis = new Redis;
$redis -> connect('127.0.0.1',6379);
echo $redis->get('test_keys')."\n";

/*
$redis = new Swoole\Redis;
$redis->connect('127.0.0.1', 6379, function ($redis, $result) {
		//echo $redis->get('abcd1');
    $redis->set('test_keys', md5('value'), function ($redis, $result) {
				echo $redis->get('test_key	');
        $redis->get('test_keys', function ($redis, $result) {
						echo '['.$result.']';
            var_dump($result);
        });
    });
});
*/
