<?php
define('GILPATH',dirname(__FILE__).'/gilFramework');
include(GILPATH.'/gilCore.php');

//$result = gilDB::findAll('select * from test');


//print_r($result);

//gilCache::set('1', 'nihso', '123');exit;

gilDB::select('test',null,null,'0,1','id as aid,name as aname');
//gilDB::join('b',array('test.id'=>'b.bid'));
//gilDB::findSql('delete from test');
$result = gilDB::findAll();
print_r($result);
print_r(gilMysql::$usedTime);