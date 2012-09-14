<?php
define('GILPATH',dirname(__FILE__).'/gilFramework');

$gilConfig = array('oo'=>false);
include(GILPATH.'/GilCore.php');

//$result = gilDB::findAll('select * from test');


//print_r($result);

//GilCache::set('1', 'nihso', '123');
//echo GilCache::get('1');
//exit;

GilDB::select('test');
GilDB::link('b', 'b', array('name'=>'name'));
//gilDB::join('b',array('test.id'=>'b.bid'));
//gilDB::findSql('delete from test');
$result = GilDB::findAll();
print_r($result);