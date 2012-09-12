<?php
class gilCache{
	static public $_cc = null;
	static public $_gilConfig = array();
	
	static function set($key,$value,$lifeTime = '-1'){self::cc() -> set($key,$value,$lifeTime);}
	static function del($key){self::cc()->del($key);}
	static function get($key){return self::cc()->get($key);}
	
	/**
	 * 引擎切换
	 * @param string $engine 引擎标识
	 */
	static public function engineSwitch($engine){
		return self::$_cc = call_user_func('gilCache'.$engine.'::_init',self::$_gilConfig);
	}
	
	/**
	 * 默认引擎的初始化
	 * Enter description here ...
	 */
	static protected function cc(){
		if(self::$_cc === null){
			self::$_cc = call_user_func('gilCache'.self::$_gilConfig['cache_config']['cacheEngine'].'::_init',self::$_gilConfig);
		}
		return self::$_cc;
	}
	
}

class gilCacheFile{
	static public $_cursor = null;
	static protected $_dir = '';
	
	private function __construct($gilConfig){
		self::$_dir = $gilConfig['cacheFile_config']['dir'];
		if(mt_rand(1, 100) <= $gilConfig['cacheFile_config']['rubblishCollectProbability']){
			$handle  = opendir(self::$_dir);  
		    while( false !== ($file = readdir($handle)))  
		    {
		    	if('cache' == substr($file, 0, 5) && substr(file_get_contents(self::$_dir . '/'.$file), 13, 10) < time()){
		    		@unlink(self::$_dir . '/'.$file);
		    	}
		    }
		    closedir($handle);
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
		$lifeTime = ($lifeTime == '-1') ? 86400000 : $lifeTime;
		$value = '<?php exit;?>'.( time() + $lifeTime ).serialize($value);
		file_put_contents(self::$_dir . '/cache_'.md5($key).'.php', $value);
	}
	
	static public function get($key){
		$value = file_get_contents(self::$_dir . '/cache_'.md5($key).'.php');
		if( substr($value, 13, 10) < time() ){
			$this -> del($key);
			return FALSE;
		}
		return unserialize(substr($value, 23)); 
	}
	
	static public function del($key){
		@unlink(self::$_dir . '/cache_'.md5($key).'.php');
	}
}