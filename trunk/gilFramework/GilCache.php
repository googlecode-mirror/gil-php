<?php
class GilCache{
	static public $_cache = null;
	
	static function set($key,$value,$lifeTime = '-1'){self::cache()->set($key,$value,$lifeTime);}
	static function del($key){self::cache()->del($key);}
	static function get($key){return self::cache()->get($key);}
	
	/**
	 * 引擎切换
	 * @param string $engine 引擎标识
	 */
	static public function engineSwitch($engine){
		global $gilConfig;
		return self::$_cache = call_user_func('GilCache'.$engine.'::_init',$gilConfig);
	}
	
	/**
	 * 默认引擎的初始化
	 * Enter description here ...
	 */
	static protected function cache(){
		global $gilConfig;
		if(self::$_cache === null){
			self::$_cache = call_user_func('GilCache'.$gilConfig['cache_config']['cacheEngine'].'::_init',$gilConfig);
			if(!self::$_cache) self::$_cache = new GilCacheUnavailable();//服务不可用
		}
		return self::$_cache;
	}
	
}

class GilCacheUnavailable{
	function __call($name,$arguments){
		return false;
	}
}