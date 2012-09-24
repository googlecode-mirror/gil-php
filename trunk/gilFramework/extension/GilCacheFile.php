<?php
/**
 * 请不要调用此类，此类是Cache扩展，请调用GilCache!
 * @author Cui
 *
 */
class GilCacheFile{
	static private $_cursor = null;
	static private $_dir = '';
	
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
	
	static public function init($gilConfig){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c($gilConfig);  
		}
		return self::$_cursor;
	}
	
	public function set($key,$value,$lifeTime){
		$lifeTime = ($lifeTime == '-1') ? 86400000 : $lifeTime;
		$value = '<?php exit;?>'.( time() + $lifeTime ).serialize($value);
		file_put_contents(self::$_dir . '/cache_'.md5($key).'.php', $value);
	}
	
	public function get($key){
		if(!is_file(self::$_dir . '/cache_'.md5($key).'.php')) return false;
		$value = file_get_contents(self::$_dir . '/cache_'.md5($key).'.php');
		if( substr($value, 13, 10) < time() ){
			self::del($key);
			return FALSE;
		}
		return unserialize(substr($value, 23)); 
	}
	
	public function del($key){
		@unlink(self::$_dir . '/cache_'.md5($key).'.php');
	}
}