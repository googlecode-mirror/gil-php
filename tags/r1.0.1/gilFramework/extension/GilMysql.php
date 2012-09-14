<?php
class GilMysql{
	
	static public $_db = null;
	
	static public $_cursor = null;
	
	static public $usedTime = 0;
	
	private function __construct(){
		global $gilConfig;
		if($gilConfig['db_config']['pconnect']) self::$_db = mysql_pconnect($gilConfig['db_config']['host'],$gilConfig['db_config']['user'],$gilConfig['db_config']['password']);
		else self::$_db = mysql_connect($gilConfig['db_config']['host'],$gilConfig['db_config']['user'],$gilConfig['db_config']['password']);
		mysql_select_db($gilConfig['db_config']['dbname'],self::$_db);
		mysql_query("set names ".$gilConfig['db_config']['charset'],self::$_db);
	}
	
	static public function _disconn(){
		mysql_close(self::$_db);
		self::$_db = null;
		self::$_cursor = null;
	} 
	
	/**
	 * 连接
	 * @param array $gilConfig
	 * @return object
	 */
	static public function _conn(){
		if(self::$_cursor === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c();  
		}
		return self::$_cursor;
	}
	
	/**
	 * 执行SQL语句
	 * @param string $sql
	 * @param object $db
	 * @return object
	 */
	static public function exec($sql){
		$sql = self::check($sql);
		$usedTimeStart = microtime();//检测SQL请求耗时，探针
		$exec = mysql_query($sql, self::$_db);
		self::$usedTime = microtime() - $usedTimeStart;//检测SQL请求耗时，存入探针
		return $exec;
	}
	
	/**
	 * 通过SQL语句，获取以列名称为键值的数组
	 * @param string $sql
	 * @param object $db
	 * @return 二维数组
	 */
	static public function getArray($sql){
		if(! $result = self::exec($sql, self::$_db) )	return array();
		if(! mysql_num_rows($result) )		return array();
		$rows = array();
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC))
			$rows[] = $row;
		mysql_free_result($result);
		return $rows;
	}
	
	/**
	 * 检测SQL是否合法并过滤非法值
	 * @param string $sql
	 * @return string
	 */
	static public function check($sql){
		global $gilConfig;
		if($gilConfig['db_readonly'] == true){
			$sql=str_ireplace(array('INSERT','UPDATE','DELETE','FILE','CREATE','ALTER','INDEX','DROP','CREATETEMPORARYTABLES','SHOWVIEW','CREATEROUTINE','ALTERROUTINE','EXECUTE','CREATEVIEW','EVENT','TRIGGER','GRANT','SUPER','PROCESS','RELOAD','SHUTDOWN','SHOWDATABASES','LOCKTABLES','REFERENCES','EPLICATIONCLIENT','REPLICATIONSLAVE','CREATEUSER'),'',$sql,$i);
			if($i>0) exit;
		}
		return $sql;
	}
	
	/**
	 * 转义危险字符
	 * @param string $word
	 * @return string
	 */
	static public function escape($word){
		return mysql_real_escape_string($word,self::$_db);
	}
}