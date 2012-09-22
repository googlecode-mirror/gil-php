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
		$sqls = array();
		foreach(self::$_writeSpace as $one){
			if($one['type'] == 'Insert'){
				$sql = 'insert into '.$one['table'].' '.
						'('.implode(',',array_flip($one['rows'])).')'.
						' VALUES('.
						'\''.implode('\',\'',$one['rows']).'\''.')';
			}
			elseif($one['type'] == 'Update'){
				echo $one['type'];
				$sql = 'update '.$one['table'].' set '.
						parent::_conditionParser($one['table'], $one['rows']).' where '.
						parent::_conditionParser($one['table'], $one['condition']);
			}
			elseif($one['type'] == 'Delete'){
				$sql = 'delete from '.$one['table'].' where '.parent::_conditionParser($one['table'], $one['condition']);
			}
			else{
				$sql = '';continue;
			}
			$sqls[] = $sql;
		}

		if($transaction) self::_connect() -> runSql('START TRANSACTION');
		foreach($sqls as $sqlObj){
			$succeed = self::_connect() -> runSql($sqlObj);
			if(!$succeed && $transaction){
				self::_connect() -> runSql('ROLLBACK'); 
				return false;
			}
		}
		if($transaction){
			self::_connect() -> runSql('COMMIT');
			self::_connect() -> runSql('END');
		}
		return $succeed;
	}
}