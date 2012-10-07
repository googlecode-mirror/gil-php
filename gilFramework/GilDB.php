<?php
/**
 * GilDB类是连接DB的基础类
 * ，该类已包含查询缓存程序，当所有语句均存在缓存时，不会连接Mysql数据库，以提升效率
 * @author Administrator
 *
 */
class GilDB{
	static public $_gilConfig = array();//全局配置
	
	static protected $_selectSpace = array();
	
	static private $_connected = null;
	static private $_cursor = null;
	
	/**
		以下参数专为关联语句设计
	 */
	static protected $_selectSpaceLink = array();
	static private $_linkLock = false;//关联查询锁
	
	/**
		以下参数专为缓存系统设计，请勿修改
	 */
	static private $_processCache = array();//进程内缓存
	static private $_queryTimeNeedle = 0;
	//end
	
	private function __construct(){
		self::$_connected = call_user_func('Gil'.self::$_gilConfig['db'].'::conn');
	}
	
	/**
	 * 关闭当前的非持久连接
	 * 注意，mysql_pconnect方式建立的连接无法使用此方法关闭
	 */
	static public function close(){
		self::_connect() -> disconn();
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
		self::$_selectSpace = array();self::$_selectSpaceLink = array();
		self::$_selectSpace['select'] = array('type'=>'select', 'table'=>$table, 'conditions'=>$conditions, 'sort'=>$sort, 'fields'=>$fields, 'limit'=>$limit);
	}
	
	/**
	 * 设置分页，设置完成后，将覆盖select()中的limit参数设置
	 * @param int $page 要取的页
	 * @param int $rowsPerPage 每页的记录数
	 */
	static public function setPager($page = 1, $rowsPerPage = 30){
		$limit = ($page-1)*$rowsPerPage.','.$rowsPerPage;
		self::$_selectSpace['select']['limit'] = $limit;
	}
	
	/**
	 * 获取分页信息，若未设置分页信息，并且select()中未传入$limit参数，将返回false值
	 * @return boolean|multitype:number
	 */
	static public function getPager(){
		$limitData = explode(',', self::$_selectSpace['select']['limit']);
		if(empty($limitData[1])){
			return false;
		}
		$rowsPerPage = $limitData[1];
		$page = (floor($limitData[0]/$rowsPerPage)+1);
		
		$whereSql = '';
		if(!empty(self::$_selectSpace['select']['conditions'])) $whereSql = ' where '.self::_conditionParser(self::$_selectSpace['select']['table'], self::$_selectSpace['select']['conditions']);
		$countResult = self::findSql('select count(*) as rows from '.self::$_selectSpace['select']['table'].$whereSql);

		return array(
				'total_count'=>(int)$countResult[0]['rows'],
				'page_size' => (int)$rowsPerPage,
				'total_page' => ceil($countResult[0]['rows']/$rowsPerPage),
				'first_page' => 1,
				'prev_page' => ($page-1),
				'next_page' => ($page+1),
				'last_page' => ceil($countResult[0]['rows']/$rowsPerPage),
				'current_page' => $page
				);		
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
		self::$_selectSpace[] = array('type'=>'link','linkto'=>$linkto,'table'=>$table,'conditions'=>$conditions,'fields'=>$fields);
		self::$_selectSpaceLink[] = array('linkto'=>$linkto,'table'=>$table,'conditions'=>$conditions,'fields'=>$fields);
	}
	
	/**
	 * 查询一行
	 * @return mixed
	 */
	static public function find(){
		$result = self::findAll();
		return array_shift($result);
	}
	
	/**
	 * 查询多行
	 * 如果存在缓存，将优先返回，而不连接数据库
	 * @return Ambigous <boolean, multitype:>|unknown
	 */
	static public function findAll(){
		if((self::$_gilConfig['db_processCache'] || self::$_gilConfig['db_resultCache'])
			 && ( $cache = self::_getQueryCache() ) != false) return $cache;//查询缓存
		$sql = is_array(self::$_selectSpace) ? self::_selectSpaceParser() : self::$_selectSpace;
		$result = self::_connect() -> getArray($sql);
		self::_link($result);
		if((self::$_gilConfig['db_processCache'] || self::$_gilConfig['db_resultCache'])) self::_setQueryCache($result);
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
	
	/**
	 * 根据表名清除读取该表的所有缓存
	 * 适合某一表有新数据插入时，需要立即显示新数据
	 * @param string $table
	 */
	static public function cleanCacheByTable($table){
		$index = GilCache::get('DBCACHE_'.$table);
		if(empty($index)) return true; 
		foreach($index as $k => $v){
			GilCache::del($k);
		}
		GilCache::del('DBCACHE_'.$table);
	}
	
	static protected function _connect(){
		if(self::$_connected === null){
			$c = __CLASS__ ;  
            self::$_cursor = new $c();  
		}
		return self::$_connected;
	}
	
	/**
	 * link查询是不进行任何缓存的，但link返回的结果保存到主查询后，是会进行缓存的
	 * Enter description here ...
	 * @param unknown_type $result
	 */
	static protected function _link(& $result){
		if(empty($result)) return false;
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
	
	static protected function _selectSpaceParser($typeFilter = false){
		$fromSql = '';$joinSql = '';$whereSql = '';$endSql = '';$fields = array();
		foreach (self::$_selectSpace as $selectSpaceItem){
			if($typeFilter != false && $selectSpaceItem['type'] != $typeFilter) continue;//过滤语句
			if($selectSpaceItem['type'] == 'select'){
				$fromSql = ' FROM ' . $selectSpaceItem['table'] . ' ';
				if(!empty($selectSpaceItem['conditions'])) $whereSql = ' where '.self::_conditionParser($selectSpaceItem['table'], $selectSpaceItem['conditions']);
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
                                if(strpos($fat, '(') || strpos($fat, ')') || strpos($fat, '.')){$fields[] = $fat;continue;}
				$fields[] = $selectSpaceItem['table'] . '.' . $fat;
			}
		}
		return 'SELECT '. implode(',',$fields) . $fromSql . $joinSql . $whereSql . $endSql;
	}
	
	static protected function _conditionParser($table, $condition, $join = false){
		if(is_string($condition)) return $condition;
		$conditionString = array();
		foreach ($condition as $key => $value){
			if($join || strpos($key, '.') !== false) $conditionString[] = $key .'='. self::_connect()->escape($value);
			else $conditionString[] = $table .'.'. $key .'='. '\''. self::_connect()->escape($value) .'\'';
		}
		return implode(' and ', $conditionString);
	}
	
	static protected function _getQueryCache(){
		$hash = 'DBCACHE_'.md5(serialize(self::$_selectSpace));
		if(self::$_gilConfig['db_processCache'] && isset(self::$_processCache[$hash])) return self::$_processCache[$hash];//优先返回进程内缓存
		if(self::$_gilConfig['db_resultCache']) return GilCache::get($hash);//若进程内缓存不存在，则返回非持久化缓存
		self::$_queryTimeNeedle = microtime();//定义一个时间起点，以检测缓存
		return false;//均不存在，返回bool false
	}
	
	static protected function _setQueryCache($result){
		$hash = 'DBCACHE_'.md5(serialize(self::$_selectSpace));
		if(self::$_gilConfig['db_processCache']) self::$_processCache[$hash] = $result;
		if(self::$_gilConfig['db_resultCache'] && ((microtime() - self::$_queryTimeNeedle)) > self::$_gilConfig['db_resultCache_config']['slowRequest']){
			GilCache::set($hash, $result, self::$_gilConfig['db_resultCache_config']['expired']);
			self::_setQueryCacheRecord($hash);
		}
	}
	
	static protected function _setQueryCacheRecord($hash){
		foreach (self::$_selectSpace as $selectSpaceItem){
			$table = $selectSpaceItem['table'];
			$index = GilCache::get('DBCACHE_'.$table);
			$index[$hash] = (self::$_gilConfig['db_resultCache_config']['expired'] + time());
			if(mt_rand(0, 10) == 1){
				foreach($index as $k => $v){
					if($v < time()) unset($index[$k]);//垃圾收集机制
				}
			}
			GilCache::set('DBCACHE_'.$table, $index, self::$_gilConfig['db_resultCache_config']['expired']);
		}
	}
}