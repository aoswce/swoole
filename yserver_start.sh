#! /bin/sh
(/usr/bin/kill -9 `ps -ef | grep -v grep | grep Yserver | awk '{print $2}'` & )  &&

(
cd /usr/local/yele-server/ &&

/usr/bin/php server/server.php >> /usr/local/yele-server/log/server.log  &&

sleep 1 &&

(/usr/bin/php server/serverClient.php >> /usr/local/yele-server/log/server.log &) &&

echo `date` >> /usr/local/yele-server/git_crontab.log
)