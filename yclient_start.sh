#! /bin/sh
(/usr/bin/kill -9 `ps -ef | grep -v grep | grep Yclient | awk '{print $2}'` & )  &&

(
cd /usr/local/yele-server/ &&

(/usr/bin/php client/client.php >> /usr/local/yele-server/git_crontab.log &) &&

echo `date` >> /usr/local/yele-server/git_crontab.log
)