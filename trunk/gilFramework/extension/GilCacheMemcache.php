<?php
class GilCacheMemcache{
	static public $_cursor = null;
	static public $_memcache = null;
	static protected $_dir = '';
	
	private function __construct($gilConfig){
		self::$_memcache = new Memcache;
		foreach ($gilConfig['cacheMemcache_config'] as $memcacheServer){
			self::$_memcache -> addServer($memcacheServer['host'], $memcacheServer['port']);
		}
	}
	
	static public function _init($gilConfig){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c($gilConfig);  
		}
		return self::$_cursor;
	}
	
	static public function set($key,$value,$lifeTime){
		$lifeTime = ($lifeTime == '-1') ? 2592000 : $lifeTime;
		return self::$_memcache -> set(md5($key),serialize(array($value)),$lifeTime);
	}
	
	static public function get($key){
		$value = self::$_memcache -> get(md5($key));
		if(empty($value)) return false;
		else return array_pop(unserialize($value)); 
	}
	
	static public function del($key){
		return self::$_memcache -> delete(md5($key));
	}
}