<?php
/*
 * 你也可以定义以下这些目录，以实现多入口
define('APPPATH',dirname(__FILE__));
define('APPCONTROLLERPATH',dirname(__FILE__).'/protected/controller');
define('APPMODELPATH',dirname(__FILE__).'/protected/model');
define('APPVIEWPATH',dirname(__FILE__).'/protected/view');
*/

//URL的访问标准是 http://xxxx/index.php?r=main/index
//默认会执行controller文件夹下的MainController.php
//然后执行该控制器类下的actionIndex()
//返回一个数组，然后程序会载入view文件夹下的main/index.php
//PS：程序不会自动调用model，你需要显式声明调用，程序会搜索model文件夹下相应model.php及class model

include('gilFramework/GilCore.php'); //只需一条语句即可使用OO编程
?>