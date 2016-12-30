#! /bin/sh
echo `git pull` >> /usr/local/yele-server/git_crontab.log &
echo `date` >> /usr/local/yele-server/git_crontab.log &
