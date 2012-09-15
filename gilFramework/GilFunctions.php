<?php

/**
 * 自动加载各种类，使用spl_autoload_register意味着你不能将GilFramework运行在PHP5.1（不含）以下版本中
 * 如有与GilFunctions冲突的__autoload，则必须在载入GilCore.php前、或在GilConfig.php中使用spl_autoload_register()方法定义好
 */
spl_autoload_register(function($mClassName){
	if(file_exists(GILPATH."/".$mClassName.".php")){
		require(GILPATH."/".$mClassName.".php");//Load Core Dir first
	}
	elseif(file_exists(GILPATH."/extension/".$mClassName.".php")){
		require(GILPATH."/extension/".$mClassName.".php");//Load Extension Dir second
	}
	elseif(defined('APPMODELPATH') && file_exists(APPMODELPATH."/".$mClassName.".php")){
		require(APPMODELPATH."/".$mClassName.".php");//Load User Defined Model Dir final
	}
	else{
		die("Error:Cannot found model {$mClassName}");
	}
});

/**
 * 从get post cookie中取出指定键的数据，并转换成整型
 * $method可以|分隔，将从左至右顺序返回存在数据
 * @param string $key
 * @param bool $unsigned 是否
 * @param string $method
 */
function GPCInt($key, $unsigned = false, $method = 'get|post'){
	$data = intval(GPC($key, $method));
	return $unsigned ? abs($data) : $data;
}

/**
 * 从get post cookie中取出指定键的数据，并原样返回
 * $method可以|分隔，将从左至右顺序返回存在数据
 * @param string $key
 * @param string $method
 */
function GPC($key, $method = 'get|post'){
	foreach (explode('|',$method) as $m){
		switch(strtolower($m)){
			case 'get':if(isset($_GET[$key])) return $_GET[$key];
			case 'post':if(isset($_POST[$key])) return $_POST[$key];
			case 'cookie':if(isset($_COOKIE[$key])) return $_COOKIE[$key];
		}
	}
	return false;
}