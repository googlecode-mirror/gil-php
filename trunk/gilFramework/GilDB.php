<?php
class GilDB{
	static public $_connected = null;
	static public $_cursor = null;
	static private $_selectSpace = array();
	static private $_selectSpaceLink = array();
	static private $_linkLock = false;
	
	private function __construct(){
		global $gilConfig;
		self::$_connected = call_user_func('Gil'.$gilConfig['db'].'::_conn');
	}
	
	/**
	 * 关闭当前的非持久连接
	 * 注意，mysql_pconnect方式建立的连接无法使用此方法关闭
	 */
	static public function close(){
		self::_connect() -> _disconn();
		self::$_connected = null;
	}
	
	/**
	 * 查询主句的建立
	 * @param string $table
	 * @param mixed $conditions
	 * @param string $sort
	 * @param string $limit
	 * @param string $fields
	 */
	static public function select($table, $conditions = array(), $sort = '', $limit = '', $fields = '*'){
		self::$_selectSpace = array();
		self::$_selectSpace[] = array('type'=>'select','table'=>$table,'conditions'=>$conditions,'sort'=>$sort,'fields'=>$fields, 'limit'=>$limit);
	}
	
	/**
	 * 联合查询 LEFT JOIN方式
	 * 适合一对一查询
	 * 建议大表驱动小表
	 * @param string $table
	 * @param mixed $conditions
	 * @param string $fields
	 */
	static public function join($table, $conditions, $fields = '*'){
		self::$_selectSpace[] = array('type'=>'join','table'=>$table,'conditions'=>$conditions,'fields'=>$fields);
	}
	
	/**
	 * 关联查询
	 * 适合一对多查询
	 * @param string $linkto 查询后的结果保存在以此为名的数组集中
	 * @param string $table 关联查询的表名
	 * @param array $conditions 只支持传入数组，以主查询键 => 关联查询表列名定义
	 * @param string $fields
	 */
	static public function link($linkto, $table, $conditions, $fields = '*'){
		self::$_selectSpaceLink[] = array('linkto'=>$linkto,'table'=>$table,'conditions'=>$conditions,'fields'=>$fields);
	}
	
	/**
	 * 查询一行
	 * @return mixed
	 */
	static public function find(){
		$sql = is_array(self::$_selectSpace) ? self::_selectSpaceParser() : self::$_selectSpace;
		$result = self::_connect() -> getArray($sql);
		self::_link($result);
		return array_pop($result);
	}
	
	/**
	 * 查询多行
	 */
	static public function findAll(){
		$sql = is_array(self::$_selectSpace) ? self::_selectSpaceParser() : self::$_selectSpace;
		$result = self::_connect() -> getArray($sql);
		self::_link($result);
		return $result;
	}
	
	/**
	 * 根据SQL语句查询多行
	 * 注意：你需要自行处理非法字符串，你也可以调用GilDB::_connect()->escape()处理
	 * @param array $sql
	 */
	static public function findSql($sql){
		return self::_connect() -> getArray($sql);
	}
	
	static protected function _connect(){
		if(self::$_connected === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c();  
		}
		return self::$_connected;
	}
	
	static protected function _link(& $result){
		if(self::$_linkLock) return false;
		self::$_linkLock = true;
		foreach(self::$_selectSpaceLink as $link){
			if(is_array($result[0])){
				//二维数组
				foreach($result as $resultKey => $resultItem){
					$conditions = array();
					foreach ($link['conditions'] as $conditionValue => $conditionKey){
						$conditions[] = $conditionKey . ' = \'' . $resultItem[$conditionValue] . '\'';
					}
					$sql = 'SELECT ' . $link['fields'] . ' FROM ' . $link['table'] . ' WHERE ' . implode(' and ',$conditions);
					$result[$resultKey][$link['linkto']] = self::findSql($sql);
				}
			}
			else{
				//一维数组
				$conditions = array();
				foreach ($link['conditions'] as $conditionValue => $conditionKey){
					$conditions[] = $conditionKey . ' = \'' . $result[$conditionValue] . '\'';
				}
				$sql = 'SELECT ' . $link['fields'] . ' FROM ' . $link['table'] . ' WHERE ' . implode(' and ',$conditions);
				$result[$link['linkto']] = self::findSql($sql);
			}
		}
		self::$_linkLock = false;
	}
	
	static protected function _selectSpaceParser(){
		$fromSql = '';$joinSql = '';$endSql = '';$fields = array();
		foreach (self::$_selectSpace as $selectSpaceItem){
			if($selectSpaceItem['type'] == 'select'){
				$fromSql = ' FROM ' . $selectSpaceItem['table'] . ' ';
				if(!empty($selectSpaceItem['sort'])) $endSql.=' ORDER BY '.$selectSpaceItem['sort'];
				if(!empty($selectSpaceItem['limit'])) $endSql.=' LIMIT '.$selectSpaceItem['limit'];
			}
			elseif($selectSpaceItem['type'] == 'join'){
				$joinSql .= ' LEFT JOIN ' . $selectSpaceItem['table']. ' ON ' . self::_conditionParser($selectSpaceItem['table'], $selectSpaceItem['conditions'], true); 
			}
			else{
				continue;
			}
			foreach (explode(',', $selectSpaceItem['fields']) as $fat){
				$fields[] = $selectSpaceItem['table'] . '.' . $fat;
			}
		}
		return 'SELECT '. implode(',',$fields) . $fromSql . $joinSql . $endSql;
	}
	
	static protected function _conditionParser($table, $condition, $join = false){
		if(is_string($condition)) return $condition;
		$conditionString = array();
		foreach ($condition as $key => $value){
			if($join) $conditionString[] = $key .'='. $value;
			else $conditionString[] = $table .'.'. $key .'='. '\''. self::_connect()->escape($value) .'\'';
		}
		return implode(' and ', $conditionString);
	}
	
}