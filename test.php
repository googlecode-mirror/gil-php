<?php
/**
 * 这个文件示例了单文件，非MVC式的编程，十分适合单次用例的快速开发
 */
define('GILPATH',dirname(__FILE__).'/gilFramework');//定义GIL框架主程序的文件夹

$gilConfig = array('oo'=>false);//在加载配置文件前写上这句，可以覆盖配置，这里的意思是强制不使用oo模式
include(GILPATH.'/GilCore.php');

//GilSession::clean();
GilSession::set('hello','hi');
//GilSession::del('hello');
echo GilSession::get('hello');
//exit;

/**
 * 我们在数据库中有2个表
 * test表 列 id name
 * b表 列bid name
 * 下面，我们来读取数据试试看
 */

$result = gilDB::findSql('select * from test');//单独地运行SQL语句，读表
//需要特别注意，这里需要自行过滤危险字符串

print_r($result);

GilCache::set('1', 'nihso', '123');//这里示范了key=>value式的缓存写入，默认使用了File引擎缓存，请在gilFramework下建立一个tmp文件夹用于存放临时文件
echo GilCache::get('1');

/**
 * 下面用伪语句的方法读取数据库，这是推荐的方式
 */
//GilDB::cleanCacheByTable('b');//这是比较高级的功能，用于清除MYSQL结果缓存
GilDB::select('test',array('id'=>1),'id asc','0,30','id,name as aname');//获取test表，条件是id为1，排序 id asc，分页0,30，获取id和name列，其中name命名为aname，这里除了表名，其它都是可选的
GilDB::link('bdata', 'b', array('aname'=>'name'),'bid,name');//关联查询，常用于一对多，这里查询b表，查询得到的结果插入到主查询数组的bdata键中，查询限制的条件是上一句的aname数据等于b表的name值，除了返回字段后，其它都是必填的
//gilDB::join('b',array('test.id'=>'b.bid'));//join查询，常用于一对一，这里表示查询b表，条件是test.id=>b.bid，注意，条件中b.bid必须放在值中
//gilDB::findSql('delete from test');//我们开启了强制只读功能，如果你将此句注释去掉，那里，程序会停止执行
$result = GilDB::findAll();//只有到这里，数据库结果才真正出来了！
print_r($result);//请留意，我们在配置文件中开启了结果缓存功能，当下次执行这个文件的时候，程序会直接读取缓存文件，关于这个，可以详看手册