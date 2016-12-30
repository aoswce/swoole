#!/bin/bash

select ch in 'winesave' 'winefetch' 'update' 'delete'
do
case $ch in
winesave)
	curl -i http://api.app-server-online.yele/seller/savewine -d '{"name": "zhen","seller_id": "B1111", "phone": "13929561341","code": "bbb","time": "2017-01-28","info": [{"id": "1","name": "洋酒And","unit": "瓶","count": "3","percent": "1","remark": ""},{"id": "2","name": "红酒Bracelet","unit": "支","count": "1","percent": "25%","remark": ""}]}'
	;;
winefetch)
	curl -i http://api.app-server-online.yele/seller/savewine -d '{"phone":"123456789","code":"code","status":1,"msg":"错误信息"}'
	;;
update)
	curl -i http://api.app-server-online.yele/seller/1 -X PATCH -d '{"template":{"data":[{"name":"i_num","value":"99"}]}}'
	;;
delete)
	curl -i http://api.app-server-online.yele/seller/1 -X DELETE
	;;
*)
	echo 'anything else is quit!'
	exit
	;;
esac

#exit
done

