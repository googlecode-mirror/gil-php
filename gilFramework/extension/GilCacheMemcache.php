<?php
/**
 * 请不要调用此类，此类是Cache扩展，请调用GilCache!
 * @author Cui
 *
 */
class GilCacheMemcache{
	static private $_cursor = null;
	static private $_memcache = null;
	
	private function __construct($gilConfig){
		self::$_memcache = new Memcache;
		$available = false;
		foreach ($gilConfig['cacheMemcache_config'] as $memcacheServer){
			self::$_memcache -> addServer($memcacheServer['host'], $memcacheServer['port']);
			if(!$available && self::$_memcache -> getServerStatus($memcacheServer['host'], $memcacheServer['port']) != false) $available = true;//检测memcache是否全部不可用
		}
		if(!$available) self::$_memcache = null;
	}
	
	static public function init($gilConfig){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c($gilConfig);  
		}
		return !self::$_memcache ? null : self::$_cursor;
	}
	
	public function set($key,$value,$lifeTime){
		$lifeTime = ($lifeTime == '-1') ? 2592000 : $lifeTime;
		return self::$_memcache -> set(md5($key),$value,$lifeTime);
	}
	
	public function get($key){
		$value = self::$_memcache -> get(md5($key));
		return empty($value) ? false : $value; 
	}
	
	public function del($key){
		return self::$_memcache -> delete(md5($key));
	}
}