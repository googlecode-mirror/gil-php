<?php 
return 
$gilConfigs = array(

	'oo' => false,//面向对象编程，如果此值为false，默认不加载GilController类

	'oo_config' => array(
		'defaultController' => 'main',//默认的控制器
		'defaultAction' => 'index',//默认的动作
	),

	'db' => 'Mysql',//数据库类型，可选Mysql,Pdo_Mysql

	/**
	 * 单库非负载均衡式配置
	 */
	'db_config' => array(
		'host'=>'localhost',//数据库地址
		'user'=>'root',//数据库用户名
		'password'=>'123456',//数据库密码
		'dbname'=>'test',//库名
		'charset'=>'utf8',//数据库编码
		'pconnect'=>false,//是否为长链接，不建议设置为true
	),
	
	'db_readonly' => true,//严格的只读模式，当为true时，禁止一切写入操作语句

	'db_processCache' => true,//进程内缓存，启用时，两条相同的SQL命令将不会重复执行
	
	'db_resultCache' => true,//结果缓存，启用时，结果将缓存至非持久化存储器中，在一定时间内，相同的SQL命令将不会重复执行
	
	/**
	 * 结果缓存的限制条件
	 * 及非持久化存储器的选择
	 */
	'db_resultCache_config' => array(
		'slowRequest' => 0.1,//将SQL执行时长大于何值时，定义为慢查询，慢查询结果将被自动缓存，单位为毫秒
		'cacheEngine' => 'File',//使用何种缓存引擎 File ,Memcache Saekvdb ......
		'expired' => 3600,//结果缓存失效时间，单位为秒
	),
	
	'cache' => true,//默认缓存开关
	
	'cache_config' => array(
		'cacheEngine' => 'File',//默认缓存引擎
		'expired' => 86400,//默认缓存失效时间
	),
	
	/**
	 * File引擎缓存的配置
	 */
	'cacheFile_config' => array(
		'dir' => GILPATH.'/tmp',//缓存文件存放的位置
		'rubblishCollectProbability' => 5,//启垃圾缓存清理器的机率，0-100表示，如10表示，100次访问中平均有10次会启动该清理器
	),
	
);
;
?>