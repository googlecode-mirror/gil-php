<?php
class gilMysql{
	/**
	 * 连接
	 * @param array $gilConfig
	 * @return object
	 */
	static public function _conn($gilConfig){
		$db = mysql_connect($gilConfig['db_config']['host'],$gilConfig['db_config']['user'],$gilConfig['db_config']['password']);
		mysql_select_db($gilConfig['db_config']['dbname'],$db);
		mysql_query("set names ".$gilConfig['db_config']['charset'],$db);
		return $db;
	}
	
	/**
	 * 执行SQL语句
	 * @param string $sql
	 * @param object $db
	 * @return object
	 */
	static public function exec($sql, $db = null){
		return mysql_query($sql, $db);
	}
	
	/**
	 * 通过SQL语句，获取以列名称为键值的数组
	 * @param string $sql
	 * @param object $db
	 * @return 二维数组
	 */
	static public function getArray($sql, $db = null){
		if(! $result = self::exec($sql, $db) )	return array();
		if(! mysql_num_rows($result) )		return array();
		$rows = array();
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC))
			$rows[] = $row;
		mysql_free_result($result);
		return $rows;
	}
}