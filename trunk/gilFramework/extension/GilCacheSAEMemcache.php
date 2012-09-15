<?php
class GilCacheSAEMemcache{
	static public $_cursor = null;
	static public $_memcache = null;
	
	private function __construct(){
		self::$_memcache = memcache_init();
	}
	
	static public function _init(){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c();  
		}
		return !self::$_memcache ? null : self::$_cursor;
	}
	
	static public function set($key,$value,$lifeTime){
		$lifeTime = ($lifeTime == '-1') ? 2592000 : $lifeTime;
		return self::$_memcache -> set(md5($key),$value,$lifeTime);
	}
	
	static public function get($key){
		$value = self::$_memcache -> get(md5($key));
		return empty($value) ? false : $value; 
	}
	
	static public function del($key){
		return self::$_memcache -> delete(md5($key));
	}
}