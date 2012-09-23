<?php
class GilCache{
	static private $_cache = null;
	
	static public function set($key,$value,$lifeTime = '-1'){self::_cache()->set($key,$value,$lifeTime);}
	static public function del($key){self::_cache()->del($key);}
	static public function get($key){return self::_cache()->get($key);}
	
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
	static protected function _cache(){
		global $gilConfig;
		if(self::$_cache === null){
			self::$_cache = call_user_func('GilCache'.$gilConfig['cache_config']['cacheEngine'].'::_init',$gilConfig);
			if(!self::$_cache) self::$_cache = new GilCacheUnavailable();//服务不可用
		}
		return self::$_cache;
	}
	
}

/**
 * 请不要调用此类，此类是Cache扩展，请调用GilCache!
 * @author Cui
 *
 */
class GilCacheUnavailable{
	function __call($name,$arguments){
		return false;
	}
}