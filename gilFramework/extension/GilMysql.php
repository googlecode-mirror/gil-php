<?php
class GilMysql{
	
	static protected $_db = null;
	
	static protected $_cursor = null;
	
	private function __construct(){
		global $gilConfig;
		if($gilConfig['db_config']['pconnect']) self::$_db = mysql_pconnect($gilConfig['db_config']['host'],$gilConfig['db_config']['user'],$gilConfig['db_config']['password']) or die('DB Connect Fail!');
		else self::$_db = mysql_connect($gilConfig['db_config']['host'],$gilConfig['db_config']['user'],$gilConfig['db_config']['password']) or die('DB Connect Fail!');
		mysql_select_db($gilConfig['db_config']['dbname'],self::$_db);
		mysql_query("set names ".$gilConfig['db_config']['charset'],self::$_db);
	}
	
	static public function disconn(){
		mysql_close(self::$_db);
		self::$_db = null;
		self::$_cursor = null;
	} 
	
	/**
	 * 连接
	 * @param array $gilConfig
	 * @return object
	 */
	static public function conn(){
		if(self::$_cursor === null){ 
            self::$_cursor = new GilMysql;  
		}
		return self::$_cursor;
	}
	
	/**
	 * 通过SQL语句，获取以列名称为键值的数组
	 * @param string $sql
	 * @param object $db
	 * @return 二维数组
	 */
	public function getArray($sql){
		if(! $result = self::_exec($sql, self::$_db) )	return array();
		if(! mysql_num_rows($result) )		return array();
		$rows = array();
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC))
			$rows[] = $row;
		mysql_free_result($result);
		return $rows;
	}
	
	/**
	 * 转义危险字符，可供外部调用
	 * @param string $word
	 * @return string
	 */
	public function escape($word){
		return mysql_real_escape_string($word,self::$_db);
	}
	
	/**
	 * 执行SQL语句
	 * @param string $sql
	 * @param object $db
	 * @return object
	 */
	static protected function _exec($sql){
		$sql = self::_check($sql);
		$exec = mysql_query($sql, self::$_db);
		return $exec;
	}
	
	/**
	 * 检测SQL是否合法并过滤非法值
	 * @param string $sql
	 * @return string
	 */
	static protected function _check($sql){
		global $gilConfig;
		if($gilConfig['db_readonly'] == true){
			$sql=str_ireplace(array('INSERT','UPDATE','DELETE','FILE','CREATE','ALTER','INDEX','DROP','CREATETEMPORARYTABLES','SHOWVIEW','CREATEROUTINE','ALTERROUTINE','EXECUTE','CREATEVIEW','EVENT','TRIGGER','GRANT','SUPER','PROCESS','RELOAD','SHUTDOWN','SHOWDATABASES','LOCKTABLES','REFERENCES','EPLICATIONCLIENT','REPLICATIONSLAVE','CREATEUSER'),'',$sql,$i);
			if($i>0) die('Gil is readonly Now!');
		}
		return $sql;
	}
}