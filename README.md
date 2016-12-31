
# yele-server 用来做restful接口服务和Socket长连接的通讯



##协议


##	优势
	1.框架基于swoole开发，并且一些IO操作底层已经封装为异步，性能极其强悍。
	2.框架底层已经封装好异步，内置mysql、redis连接池，只需要在调用的时候在前面加yield，近乎同步的写法，却是异步的调用，并且无需关注底层实现，连接数超等问题，使用非常简单。
	
	
## 注意事项

	1.框架最新加入协程+mysql连接池，非阻塞的mysql查询大大提高了框架应对请求的吞吐量
	2.php版本需要7.0+
	3.swoole版本1.9.*
	4.如果用到异步redis，需要安装hiredis，安装教程:http://wiki.swoole.com/wiki/page/p-redis.html

##安装依赖包
	composer install
	1.没有安装composer的先安装composer
	2.不会composer或者不喜欢composer的可以直接去我另一个资源库下载框架依赖,地址：https://github.com/keaixiaou/zphp
	
##运行web-server

	本框架只支持http模式：
	运行：
	cd 到根目录
	php webroot/main.php start|stop|restart|reload
	访问IP:PORT

## 


