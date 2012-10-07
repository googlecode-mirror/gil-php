<?php
/**
 * GilDBW是对数据库进行写操作的类
 * @author Pony
 *
 */
class GilDBW extends GilDB{
	static protected $_writeSpace = array();
	
	static public function insert($table, $rows = array()){
		self::$_writeSpace[] = array('type'=>'Insert', 'table'=>$table, 'rows'=>$rows);
	}
	
	static public function update($table, $condition = array(), $rows = array()){
		self::$_writeSpace[] = array('type'=>'Update', 'table'=>$table, 'condition'=>$condition, 'rows'=>$rows);
	}
	
	static public function delete($table, $condition = array()){
		self::$_writeSpace[] = array('type'=>'Delete', 'table'=>$table, 'condition'=>$condition);
	}
	
	/**
	 * 保存所有更改，第一个参数是事务开关
	 * Enter description here ...
	 * @param string $transaction
	 */
	static public function save($transaction = false){
                if(empty(self::$_writeSpace)) return true;//没有动作语句，返回成功
		$sqls = array();
		foreach(self::$_writeSpace as $one){
			if($one['type'] == 'Insert'){
				$keys = array();
				foreach($one['rows'] as $key => $value) $keys[] = $key;
				$sql = 'insert into '.$one['table'].' '.
						'('.implode(',',$keys).')'.
						' VALUES('.
						'\''.implode('\',\'',$one['rows']).'\''.')';
			}
			elseif($one['type'] == 'Update'){
				$rows = array();
				foreach($one['rows'] as $key => $value) $rows[] = $key.'='.'\''.$value.'\''; 
				$sql = 'update '.$one['table'].' set '.
						implode(',',$rows).' where '.
						parent::_conditionParser($one['table'], $one['condition']);
			}
			elseif($one['type'] == 'Delete'){
				$sql = 'delete from '.$one['table'].' where '.parent::_conditionParser($one['table'], $one['condition'].';');
			}
			else{
				$sql = '';continue;
			}
			$sqls[] = $sql;
		}
		if($transaction){
			self::_connect() -> runSql('START TRANSACTION');
			foreach($sqls as $sqlObj){
				$succeed = self::_connect() -> runSql($sqlObj);
				if(!$succeed){
					self::_connect() -> runSql('ROLLBACK');
					self::_connect() -> runSql('END');
					return false;
				}
			}
			self::_connect() -> runSql('COMMIT');
			self::_connect() -> runSql('END');
		}
		else{
			foreach($sqls as $sqlObj) $succeed = self::_connect() -> runSql($sqlObj);
		}
		//表级的缓存清扫
		if(self::$_gilConfig['db_resultCache']){
			foreach (self::$_writeSpace as $one)
				self::cleanCacheByTable($one['table']);
		}
		//end
		if($succeed) self::$_writeSpace = array();//清空所有请求
		return $succeed;
	}
}