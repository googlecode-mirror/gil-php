<?php
class gilDB{
	static public $_connected = null;
	static public $_cursor = null;
	static private $_selectSpace = array();
	
	private function __construct(){
		global $gilConfig;
		self::$_connected = call_user_func('gil'.$gilConfig['db'].'::_conn');
	}
	
	static public function close(){
		self::_connect() -> _disconn();
		self::$_connected = null;
	}
	
	static public function select($table, $conditions = array(), $sort = '', $limit = '', $fields = '*'){
		self::$_selectSpace = array();
		self::$_selectSpace[] = array('type'=>'select','table'=>$table,'conditions'=>$conditions,'sort'=>$sort,'fields'=>$fields, 'limit'=>$limit);
	}
	
	static public function join($table, $conditions, $fields = '*'){
		self::$_selectSpace[] = array('type'=>'join','table'=>$table,'conditions'=>$conditions,'fields'=>$fields);
	}
	
	static public function find(){
		$sql = is_array(self::$_selectSpace) ? self::_selectSpaceParser() : self::$_selectSpace;
		$result = self::_connect() -> getArray($sql);
		return array_pop($result);
	}
	
	static public function findAll(){
		$sql = is_array(self::$_selectSpace) ? self::_selectSpaceParser() : self::$_selectSpace;
		return self::_connect() -> getArray($sql);
	}
	
	static public function findSql($sql){
		self::$_selectSpace = $sql;
		return self::findAll();
	}
	
	static protected function _connect(){
		if(self::$_connected === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c();  
		}
		return self::$_connected;
	}
	
	static protected function _selectSpaceParser(){
		$fromSql = '';$joinSql = '';$endSql = '';$fields = array();
		foreach (self::$_selectSpace as $selectSpaceItem){
			if($selectSpaceItem['type'] == 'select'){
				$fromSql = ' FROM ' . $selectSpaceItem['table'] . ' ';
				if(!empty($selectSpaceItem['sort'])) $endSql.=' ORDER BY '.$selectSpaceItem['sort'];
				if(!empty($selectSpaceItem['limit'])) $endSql.=' LIMIT '.$selectSpaceItem['limit'];
			}
			if($selectSpaceItem['type'] == 'join'){
				$joinSql .= ' LEFT JOIN ' . $selectSpaceItem['table']. ' ON ' . self::_conditionParser($selectSpaceItem['table'], $selectSpaceItem['conditions'], true); 
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
			else $conditionString[] = $table .'.'. $key .'='. '\''.$value.'\'';
		}
		return implode(' and ', $conditionString);
	}
	
}