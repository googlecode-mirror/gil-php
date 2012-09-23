<?php
/**
 * GilSession 会话数据基类
 * @author Cui
 *
 */
class GilSession{
	static private $_session = null;

	/**
	 * 取得SESSION中某值
	 * @param string $key
	 */
	static public function get($key){
		return self::_init() -> get($key);
	}

	/**
	 * 设置SESSION中某值
	 * @param string $key
	 * @param string $value
	 */
	static public function set($key, $value){
		self::_init() -> set($key, $value);
	}

	/**
	 * 删除SESSION中某值
	 * @param unknown_type $key
	 */
	static public function del($key){
		self::_init() -> del($key);
	}

	/**
	 * 清空本次SESSION会话，删除所有会话数据
	 */
	static public function clean(){
		self::_init() -> clean();
	}
	
	static private function _init(){
		if(self::$_session == null){
			global $gilConfig;
			self::$_session = call_user_func('GilSession'.$gilConfig['session_config']['sessionEngine'].'::init',$gilConfig);
			if(!self::$_session) self::$_session = new GilSessionUnavailable();
		}
		return self::$_session;
	}
}

/**
 * 请不要调用此类，此类是扩展类，请调用GilSession类 !
 * @author Cui
 *
 */
class GilSessionUnavailable{
	function __call($name,$arguments){
		return false;
	}
}

/**
 * 请不要调用此类，此类是扩展类，请调用GilSession类 !
 * @author Cui
 *
 */
class GilSessionSystem{
	static private $_cursor = null;

	private function __construct(){
		session_start();
	}

	static public function init(){
		if(self::$_cursor === null){ 
            self::$_cursor = new GilSessionSystem;
		}
		return self::$_cursor;
	}

	public function set($key, $value){
		$_SESSION[$key] = $value;
	}

	public function get($key){
		return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
	}

	public function del($key){
		unset($_SESSION[$key]);
	}

	public function clean(){
		session_destroy();
	}
}

/**
 * 请不要调用此类，此类是扩展类，请调用GilSession类 !
 * @author Cui
 *
 */
class GilSessionCache{
	static private $_cursor = null;
	static private $_sessionid = null;
	static private $_sessionData = array();

	private function __construct(){
		self::$_sessionid = getGPC('sessionid','cookie');
		if(empty(self::$_sessionid)){
			self::$_sessionid = uniqid('session_', true);
			setGPC('sessionid',self::$_sessionid);
		}
		else{
			self::$_sessionData = GilCache::get(self::$_sessionid);
		}
	}

	static public function init(){
		if(self::$_cursor === null){ 
            self::$_cursor = new GilSessionCache;
		}
		return self::$_cursor;
	}

	public function set($key, $value){
		self::$_sessionData[$key] = $value;
		GilCache::set(self::$_sessionid, self::$_sessionData);
	}

	public function get($key){
		return isset(self::$_sessionData[$key]) ? self::$_sessionData[$key] : false;
	}

	public function del($key){
		unset(self::$_sessionData[$key]);
		GilCache::set(self::$_sessionid, self::$_sessionData);
	}

	public function clean(){
		setGPC('sessionid','',-1);
		GilCache::del(self::$_sessionid);
	}
}