<?php
//GilSession is Session Base
class GilSession{
	static public $_session = null;

	static public function _init(){
		if(self::$_session == null){
			global $gilConfig;
			self::$_session = call_user_func('GilSession'.$gilConfig['session_config']['sessionEngine'].'::_init',$gilConfig);
			if(!self::$_session) self::$_session = new GilSessionUnavailable();
		}
		return self::$_session;
	}

	static public function get($key){
		return self::_init() -> get($key);
	}

	static public function set($key, $value){
		self::_init() -> set($key, $value);
	}

	static public function del($key){
		self::_init() -> del($key);
	}

	static public function clean(){
		self::_init() -> clean();
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
	static public $_cursor = null;

	private function __construct(){
		session_start();
	}

	static public function _init(){
		if(self::$_cursor === null){ 
            $c = __CLASS__ ;  
            self::$_cursor = new $c();
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
	static public $_cursor = null;
	static public $_sessionid = null;
	static public $_sessionData = array();

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

	static public function _init(){
		if(self::$_cursor === null){ 
            $c = __CLASS__ ;  
            self::$_cursor = new $c();
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