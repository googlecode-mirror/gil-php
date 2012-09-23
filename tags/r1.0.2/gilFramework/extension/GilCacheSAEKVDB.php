<?php
/**
 * 请不要调用此类，此类是Cache扩展，请调用GilCache!
 * @author Cui
 *
 */
class GilCacheSAEKVDB{
	static private $_cursor = null;
	static private $_kvdb = null;
	
	private function __construct($gilConfig){
		self::$_kvdb = new SaeKV();
		self::$_kvdb -> init();
		if(mt_rand(1, 100) <= $gilConfig['cacheSAEKVDB_config']['rubblishCollectProbability']){
			//启动垃圾收集器
			$ret = self::$_kvdb -> pkrget('cache_', 100);
			while (true) {
				foreach($ret as $key => $value){
					if( substr($value, 0, 10) < time() ){
						self::$_kvdb -> delete($key);
					}
				}
				end($ret);
				$start_key = key($ret);
				$i = count($ret);
				if ($i < 100) break;
				$ret = self::$_kvdb->pkrget('', 100, $start_key);
			}
		}
	}
	
	static public function _init($gilConfig){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c($gilConfig);  
		}
		return !self::$_kvdb ? null : self::$_cursor;
	}
	
	public function set($key,$value,$lifeTime){
		$lifeTime = ($lifeTime == '-1') ? 86400000 : $lifeTime;
		$value = ( time() + $lifeTime ).serialize($value);
		return self::$_kvdb -> set('cache_'.md5($key),$value);
	}
	
	public function get($key){
		$value = self::$_kvdb -> get('cache_'.md5($key));
		if( substr($value, 0, 10) < time() ){
			self::del($key);
			return FALSE;
		}
		return unserialize(substr($value, 10));
	}
	
	public function del($key){
		return self::$_kvdb -> delete('cache_'.md5($key));
	}
}