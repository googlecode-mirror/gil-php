<?php 
return 
$gilConfigs = array(

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

);
;
?>